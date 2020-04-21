<?php

namespace Krak\SymfonyMessengerAutoScale\PoolControl;

use Krak\SymfonyMessengerAutoScale\PoolControlFactory;
use Psr\SimpleCache\CacheInterface;

final class PsrSimpleCachePoolControlFactory implements PoolControlFactory
{
    private $cache;
    private $additionalKeyPrefix;

    public function __construct(CacheInterface $cache, string $additionalKeyPrefix = '') {
        $this->cache = $cache;
        $this->additionalKeyPrefix = $additionalKeyPrefix;
    }

    public function createForWorker(string $poolName): WorkerPoolControl {
        return new PsrSimpleCachePoolControl($this->cache, $this->additionalKeyPrefix . $poolName);
    }

    public function createForActor(string $poolName): ActorPoolControl {
        return new PsrSimpleCachePoolControl($this->cache, $this->additionalKeyPrefix . $poolName);
    }
}
