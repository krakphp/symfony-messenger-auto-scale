<?php

namespace Krak\SymfonyMessengerAutoScale\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControl;
use Krak\SymfonyMessengerAutoScale\PoolStatus;

/**
 * Access to the PoolControl from the Worker which
 * actually dictates and responds to state change requests
 */
interface WorkerPoolControl extends PoolControl
{
    public function scaleWorkers(int $numWorkers): void;
    public function updateStatus(PoolStatus $poolStatus, ?int $sizeOfQueues = null): void;
}
