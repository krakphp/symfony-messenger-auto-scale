<?php

namespace Krak\SymfonyMessengerAutoScale\DependencyInjection;

use Krak\SymfonyMessengerAutoScale\Internal\Glob;
use Krak\SymfonyMessengerAutoScale\MessengerAutoScaleBundle;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class BuildSupervisorPoolConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) {
        $receiverNames = $this->findReceiverNames($container);

        $this->registerSupervisorPoolConfigsToTaggedServices($container, $receiverNames);
        $container->setParameter('messenger_auto_scale.receiver_names', $receiverNames);
    }

    /**
     * Any service that uses the SUPERVISOR_POOL_CONFIGS tag will be automatically injected with a configured array of
     * SupervisorPoolConfig objects.
     * We need to use a service factory to support this because you can't have objects as parameters in SF DI.
     */
    private function registerSupervisorPoolConfigsToTaggedServices(ContainerBuilder $container, array $receiverNames): void {
        $rawPoolConfig = $container->getParameter('krak.messenger_auto_scale.config.pools');
        $supervisorPoolConfigs = $this->buildSupervisorPoolConfigs($rawPoolConfig, $receiverNames);
        $container->findDefinition('krak.messenger_auto_scale.supervisor_pool_configs')->addArgument(iterator_to_array($supervisorPoolConfigs));
    }

    /** @return SupervisorPoolConfig[] */
    private function buildSupervisorPoolConfigs(array $rawPoolConfig, array $receiverNames): iterable {
        foreach ($rawPoolConfig['pools'] as $poolName => $rawPool) {
            if (!count($receiverNames)) {
                throw new \LogicException('No receivers/transports are left to match pool config - ' . $poolName);
            }

            [$matchedReceiverNames, $receiverNames] = $this->matchReceiverNameFromRawPool($rawPool, $receiverNames);
            yield ['name' => $poolName, 'poolConfig' => $rawPool, 'receiverIds' => $matchedReceiverNames];
        }
    }

    /** return the matched receiver names and unmatched recevier names as a two tuple. */
    private function matchReceiverNameFromRawPool(array $rawPool, array $receiverNames): array {
        $matched = [];
        $unmatched = [];
        $glob = new Glob($rawPool['receivers']);
        foreach ($receiverNames as $receiverName)  {
            if ($glob->matches($receiverName)) {
                $matched[] = $receiverName;
            } else {
                $unmatched[] = $receiverName;
            }
        }

        return [$matched, $unmatched];
    }

    private function findReceiverNames(ContainerBuilder $container): array {
        $receiverMapping = [];
        foreach ($container->findTaggedServiceIds('messenger.receiver') as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['alias'])) {
                    $receiverMapping[$tag['alias']] = null;
                }
            }
        }

        return \array_unique(\array_keys($receiverMapping));
    }

    public static function createSupervisorPoolConfigsFromArray(array $poolConfigs): array {
        return \array_map(function(array $pool) {
            return new SupervisorPoolConfig($pool['name'], PoolConfig::createFromOptionsArray($pool['poolConfig']), $pool['receiverIds']);
        }, $poolConfigs);
    }
}
