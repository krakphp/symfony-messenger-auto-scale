<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Bundle;

use Krak\SymfonyMessengerAutoScale\MessengerAutoScaleBundle;
use Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\TestFixtureBundle;
use Krak\SymfonyMessengerRedis\MessengerRedisBundle;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait InitsKernel
{
    protected function getBundleClass() {
        return MessengerAutoScaleBundle::class;
    }

    private function registerPublicServiceCompilerPass() {
        $this->addCompilerPass(new PublicServicePass('/(Krak.*|krak\..*|messenger.default_serializer|.*MessageBus.*)/'));
    }

    private function given_the_kernel_is_booted_with_config_resources(array $configResources) {
        $kernel = $this->createKernel();
        $kernel->addBundle(TestFixtureBundle::class);
        $kernel->addBundle(MessengerRedisBundle::class);
        foreach ($configResources as $config) {
            $kernel->addConfigFile($config);
        }
        $this->bootKernel();
    }

    private function given_the_kernel_is_booted_with_messenger_and_auto_scale_config() {
        $this->given_the_kernel_is_booted_with_config_resources([
            __DIR__ . '/../Fixtures/messenger-config.yaml',
            __DIR__ . '/../Fixtures/auto-scale-config.yaml',
        ]);
    }
}
