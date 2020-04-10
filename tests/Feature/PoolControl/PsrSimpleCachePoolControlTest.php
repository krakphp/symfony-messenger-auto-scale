<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControl;
use Krak\SymfonyMessengerAutoScale\Tests\Feature\PoolControlTestOutline;
use Symfony\Component\Cache\{Adapter\ArrayAdapter, Psr16Cache};

final class PsrSimpleCachePoolControlTest extends PoolControlTestOutline
{
    public function createPoolControls(): array {
        $control = new PoolControl\PsrSimpleCachePoolControl(new Psr16Cache(new ArrayAdapter()), 'test');
        return [$control, $control];
    }
}
