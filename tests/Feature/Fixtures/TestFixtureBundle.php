<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class TestFixtureBundle extends Bundle
{
    public function getContainerExtension() {
        return new class() extends Extension {
            public function getAlias() {
                return 'messenger_auto_scale_test';
            }

            public function load(array $configs, ContainerBuilder $container) {
                $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
                $loader->load('services.php');
            }
        };
    }
}
