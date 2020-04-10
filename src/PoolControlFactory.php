<?php

namespace Krak\SymfonyMessengerAutoScale;

use Krak\SymfonyMessengerAutoScale\PoolControl\ActorPoolControl;
use Krak\SymfonyMessengerAutoScale\PoolControl\WorkerPoolControl;

interface PoolControlFactory
{
    public function createForWorker(string $poolName): WorkerPoolControl;
    public function createForActor(string $poolName): ActorPoolControl;
}
