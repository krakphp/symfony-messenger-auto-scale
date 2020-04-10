<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControl;
use Krak\SymfonyMessengerAutoScale\Tests\Feature\PoolControlTestOutline;

final class InMemoryPoolControlTest extends PoolControlTestOutline
{
    public function createPoolControls(): array {
        $control = new PoolControl\InMemoryPoolControl();
        return [$control, $control];
    }
}
