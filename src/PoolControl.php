<?php

namespace Krak\SymfonyMessengerAutoScale;

interface PoolControl
{
    public function getStatus(): PoolStatus;
    public function getNumWorkers(): int;
    public function getSizeOfQueues(): ?int;
    public function getPoolConfig(): ?PoolConfig;
    public function shouldStop(): bool;
}
