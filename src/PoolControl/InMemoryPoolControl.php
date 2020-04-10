<?php

namespace Krak\SymfonyMessengerAutoScale\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\PoolStatus;

final class InMemoryPoolControl implements ActorPoolControl, WorkerPoolControl
{
    private $numWorkers;
    private $status;
    private $poolConfig;
    private $shouldStop;
    private $shouldPause;
    private $sizeOfQueues;

    public function __construct(?PoolConfig $poolConfig = null) {
        $this->numWorkers = 0;
        $this->poolConfig = $poolConfig;
        $this->status = PoolStatus::stopped();
        $this->shouldStop = false;
        $this->shouldPause = false;
    }

    public function getStatus(): PoolStatus {
        return $this->status;
    }

    public function getNumWorkers(): int {
        return $this->numWorkers;
    }

    public function getSizeOfQueues(): ?int {
        return $this->sizeOfQueues;
    }

    public function getPoolConfig(): ?PoolConfig {
        return $this->poolConfig;
    }

    public function shouldStop(): bool {
        return $this->shouldStop || $this->shouldPause;
    }

    public function scaleWorkers(int $numWorkers): void {
        $this->numWorkers = $numWorkers;
    }

    public function updatePoolConfig(?PoolConfig $poolConfig): void {
        $this->poolConfig = $poolConfig;
    }

    public function restart(): void {
        $this->shouldStop = true;
    }

    public function pause(): void {
        $this->shouldPause = true;
    }

    public function resume(): void {
        $this->shouldPause = false;
    }

    public function updateStatus(PoolStatus $poolStatus, ?int $sizeOfQueues = null): void {
        $this->status = $poolStatus;
        $this->shouldStop = false;
        $this->sizeOfQueues = $sizeOfQueues;
    }
}
