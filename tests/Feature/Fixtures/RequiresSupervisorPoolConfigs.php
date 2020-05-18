<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures;

use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;

final class RequiresSupervisorPoolConfigs
{
    public $poolConfigs;
    public $receiverToPoolMapping;

    /** @param SupervisorPoolConfig[] $poolConfigs */
    public function __construct(array $poolConfigs, array $receiverToPoolMapping) {
        $this->poolConfigs = $poolConfigs;
        $this->receiverToPoolMapping = $receiverToPoolMapping;
    }
}
