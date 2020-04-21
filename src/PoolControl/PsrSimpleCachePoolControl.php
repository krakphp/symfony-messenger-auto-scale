<?php

namespace Krak\SymfonyMessengerAutoScale\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControl;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\PoolStatus;
use Psr\SimpleCache\CacheInterface;

final class PsrSimpleCachePoolControl implements ActorPoolControl, WorkerPoolControl
{
    private $cache;
    private $keyScope;

    public function __construct(CacheInterface $cache, string $keyScope) {
        $this->cache = $cache;
        $this->keyScope = $keyScope;
    }

    private function key(string $key): string {
        return 'messenger_auto_scale_consumer_control_' . $this->keyScope . '_' . $key;
    }

    public function getStatus(): PoolStatus {
        return $this->cache->get($this->key('status'), PoolStatus::stopped());
    }

    public function getNumWorkers(): int {
        return $this->cache->get($this->key('num_workers'), 0);
    }

    public function getSizeOfQueues(): ?int {
        return $this->cache->get($this->key('size_of_queues'), null);
    }

    public function getPoolConfig(): ?PoolConfig {
        return $this->cache->get($this->key('pool_config'), null);
    }

    public function shouldStop(): bool {
        return
            $this->cache->get($this->key('should_stop'), false)
            || $this->cache->get($this->key('should_pause'), false);
    }

    public function scaleWorkers(int $numWorkers): void {
        $this->cache->set($this->key('num_workers'), $numWorkers);
    }

    public function updatePoolConfig(?PoolConfig $poolConfig): void {
        $this->cache->set($this->key('pool_config'), $poolConfig);
    }

    public function restart(): void {
        $this->cache->set($this->key('should_stop'), true);
    }

    public function pause(): void {
        $this->cache->set($this->key('should_pause'), true);
    }

    public function resume(): void {
        $this->cache->set($this->key('should_pause'), false);
    }

    public function updateStatus(PoolStatus $poolStatus, ?int $sizeOfQueues = null): void {
        $this->cache->setMultiple([
            $this->key('status') => $poolStatus,
            $this->key('should_stop') => false,
            $this->key('size_of_queues') => $sizeOfQueues,
        ]);
    }
}
