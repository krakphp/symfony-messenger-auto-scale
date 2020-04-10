<?php

namespace Krak\SymfonyMessengerAutoScale\AutoScale;

use Krak\SymfonyMessengerAutoScale\PoolConfig;

final class AutoScaleRequest
{
    private $state;
    private $timeSinceLastCall;
    private $numProcs;
    private $sizeOfQueue;
    private $poolConfig;

    public function __construct(?array $state, ?int $timeSinceLastCall, int $numProcs, int $sizeOfQueue, PoolConfig $poolConfig) {
        $this->state = $state;
        $this->timeSinceLastCall = $timeSinceLastCall;
        $this->numProcs = $numProcs;
        $this->sizeOfQueue = $sizeOfQueue;
        $this->poolConfig = $poolConfig;
    }

    public function state(): ?array {
        return $this->state;
    }

    public function timeSinceLastCall(): ?int {
        return $this->timeSinceLastCall;
    }

    public function numProcs(): int {
        return $this->numProcs;
    }

    public function sizeOfQueue(): int {
        return $this->sizeOfQueue;
    }

    public function poolConfig(): PoolConfig {
        return $this->poolConfig;
    }
}
