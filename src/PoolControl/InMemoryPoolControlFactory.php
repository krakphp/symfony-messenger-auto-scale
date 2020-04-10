<?php

namespace Krak\SymfonyMessengerAutoScale\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControlFactory;

final class InMemoryPoolControlFactory implements PoolControlFactory
{
    private $poolControl;

    public function createForWorker(string $poolName): WorkerPoolControl {
        return $this->poolControl ? $this->poolControl : $this->poolControl = new InMemoryPoolControl();
    }

    public function createForActor(string $poolName): ActorPoolControl {
        return $this->poolControl ? $this->poolControl : $this->poolControl = new InMemoryPoolControl();
    }
}
