<?php

namespace Krak\SymfonyMessengerAutoScale\Event;

final class PoolBackedUpAlert
{
    private $poolName;
    private $thresholdLimit;
    private $currentNumberOfMessages;

    public function __construct(string $poolName, int $thresholdLimit, int $currentNumberOfMessages) {
        $this->poolName = $poolName;
        $this->thresholdLimit = $thresholdLimit;
        $this->currentNumberOfMessages = $currentNumberOfMessages;
    }

    public function poolName(): string {
        return $this->poolName;
    }

    public function thresholdLimit(): int {
        return $this->thresholdLimit;
    }

    public function currentNumberOfMessages(): int {
        return $this->currentNumberOfMessages;
    }
}
