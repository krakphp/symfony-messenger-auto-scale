<?php

namespace Krak\SymfonyMessengerAutoScale;

use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Config\FileLocator;
use function Krak\Schema\{
    bool,
    dict,
    struct,
    int,
    listOf,
    string,
    ProcessSchema\SymfonyConfig\configTree};

class MessengerAutoScaleBundle extends Bundle
{
    const TAG_SUPERVISOR_POOL_CONFIGS = 'messenger_auto_scale.supervisor_pool_configs';
    const TAG_RAISE_ALERTS = 'messenger_auto_scale.raise_alerts';

    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new DependencyInjection\BuildSupervisorPoolConfigCompilerPass());
        $container->registerForAutoconfiguration(RaiseAlerts::class)->addTag(self::TAG_RAISE_ALERTS);
    }

    public function getContainerExtension(): ExtensionInterface {
        return new class() extends Extension {
            public function getAlias(): string {
                return 'messenger_auto_scale';
            }

            /** @param mixed[] $configs */
            public function load(array $configs, ContainerBuilder $container): void {
                $configuration = $this->getConfiguration($configs, $container);
                $processedConfig = $this->processConfiguration($configuration, $configs);
                $this->loadServices($container);

                // processed pool config to be accessible as a parameter.
                $container->setParameter('krak.messenger_auto_scale.config.pools', $processedConfig);
                $container->findDefinition(ProcessManager\SymfonyMessengerProcessManagerFactory::class)
                    ->addArgument($processedConfig['console_path']);
            }

            public function getConfiguration(array $config, ContainerBuilder $container) {
                return new class() implements ConfigurationInterface {
                    public function getConfigTreeBuilder() {
                        return configTree('messenger_auto_scale', struct([
                            'console_path' => string(['configure' => function(ScalarNodeDefinition $def) {
                                $def->defaultValue('%kernel.project_dir%/bin/console');
                            }]),
                            'must_match_all_receivers' => bool(['configure' => function(BooleanNodeDefinition $def) {
                                $def->defaultTrue();
                            }]),
                            'pools' => dict(struct([
                                'min_procs' => int(),
                                'max_procs' => int(),
                                'message_rate' => int(),
                                'scale_up_threshold_seconds' => int(),
                                'scale_down_threshold_seconds' => int(),
                                'worker_command' => string(),
                                'worker_command_options' => listOf(string()),
                                'backed_up_alert_threshold' => int(),
                                'receivers' => string(),
                            ], ['allowExtraKeys' => true]))
                        ]));
                    }
                };
            }

            private function loadServices(ContainerBuilder $container): void {
                $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
                $loader->load('services.xml');
            }
        };
    }
}
