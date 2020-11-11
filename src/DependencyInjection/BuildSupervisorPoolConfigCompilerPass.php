<?php

namespace Krak\SymfonyMessengerAutoScale\DependencyInjection;

use Krak\SymfonyMessengerAutoScale\Internal\Glob;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;
use Symfony\Bundle\FrameworkBundle;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BuildSupervisorPoolConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) {
        $receiverNames = $this->findReceiverNamesSortedByPriorityAndPosition($container);
        $this->registerMappedPoolConfigData($container, $receiverNames);
        $container->setParameter('messenger_auto_scale.receiver_names', $receiverNames);
    }

    /**
     * Certain help array structures need to built from the original pool config data. To make these structures shareable
     * we register them as services factories which are responsible for transforming the array of supervisor pool config
     * into the necessary structure.
     * @see BuildSupervisorPoolConfigCompilerPass::createSupervisorPoolConfigsFromArray()
     * @see BuildSupervisorPoolConfigCompilerPass::createReceiverToPoolMappingFromArray()
     */
    private function registerMappedPoolConfigData(ContainerBuilder $container, array $receiverNames): void {
        $rawPoolConfig = $container->getParameter('krak.messenger_auto_scale.config.pools');
        $supervisorPoolConfigs = iterator_to_array($this->buildSupervisorPoolConfigs($rawPoolConfig, $receiverNames));
        $container->findDefinition('krak.messenger_auto_scale.supervisor_pool_configs')->addArgument($supervisorPoolConfigs);
        $container->findDefinition('krak.messenger_auto_scale.receiver_to_pool_mapping')->addArgument($supervisorPoolConfigs);
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

        if (count($receiverNames) && $rawPoolConfig['must_match_all_receivers']) {
            throw new \LogicException('Some receivers were not matched by the pool config: ' . implode(', ', $receiverNames));
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

    private function findReceiverNamesSortedByPriorityAndPosition(ContainerBuilder $container): array {
        $frameworkConfig = (new Processor())->processConfiguration(
            new FrameworkBundle\DependencyInjection\Configuration($container->getParameter('kernel.debug')),
            $container->getExtensionConfig('framework')
        );
        $transports = $frameworkConfig['messenger']['transports'] ?? [];
        $receiverNamesToSort = [];
        $position = 0;
        foreach ($transports as $transportName => $config) {
            $receiverNamesToSort[] = [
                'name' => $transportName,
                'priority' => $config['options']['priority'] ?? 0,
                'position' => $position
            ];
            $position += 1;
        }
        usort($receiverNamesToSort, function(array $a, array $b) {
            // sort by priority desc, position asc
            return ($b['priority'] <=> $a['priority']) ?: ($a['position'] <=> $b['position']);
        });
        return array_column($receiverNamesToSort, 'name');
    }

    public static function createSupervisorPoolConfigsFromArray(array $poolConfigs): array {
        return \array_map(function(array $pool) {
            return new SupervisorPoolConfig($pool['name'], PoolConfig::createFromOptionsArray($pool['poolConfig']), $pool['receiverIds']);
        }, $poolConfigs);
    }

    public static function createReceiverToPoolMappingFromArray(array $poolConfigs): array {
        $mapping = [];
        foreach ($poolConfigs as $pool) {
            foreach ($pool['receiverIds'] as $receiverId) {
                $mapping[$receiverId] = $pool['name'];
            }
        }
        return $mapping;
    }
}
