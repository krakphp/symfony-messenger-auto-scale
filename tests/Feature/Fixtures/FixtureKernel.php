<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures;

use Krak\SymfonyMessengerAutoScale\MessengerAutoScaleBundle;
use Krak\SymfonyMessengerRedis\MessengerRedisBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class FixtureKernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;

    public function getProjectDir() {
        return __DIR__ . '/../../..';
    }

    public function registerBundles() {
        yield new FrameworkBundle();
        yield new MessengerRedisBundle();
        yield new MessengerAutoScaleBundle();
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) {
        // TODO: Implement configureRoutes() method.
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader) {
        $loader->load(__DIR__ . '/messenger-config.yaml');
        $loader->load(__DIR__ . '/auto-scale-config.yaml');
        $loader->load(__DIR__ . '/services.php');
    }
}
