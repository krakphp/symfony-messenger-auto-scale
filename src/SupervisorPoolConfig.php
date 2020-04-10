<?php

namespace Krak\SymfonyMessengerAutoScale;

/**
 * Wrapper of the pool config used by the supervisor and setup of the system.
 * We don't want to include receivers as part of the pool config because pool config is only
 * used by workers (which don't care about receivers) and by UI actors which shouldn't be modifying the
 * receivers.
 */
final class SupervisorPoolConfig
{
    private $name;
    private $poolConfig;
    private $receiverIds;

    public function __construct(string $name, PoolConfig $poolConfig, array $receiverIds) {
        $this->name = $name;
        $this->poolConfig = $poolConfig;
        $this->receiverIds = $receiverIds;
    }

    public function name(): string {
        return $this->name;
    }

    public function poolConfig(): PoolConfig {
        return $this->poolConfig;
    }

    /** @rerturn string[] */
    public function receiverIds(): array {
        return $this->receiverIds;
    }
}
