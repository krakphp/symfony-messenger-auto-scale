<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures;

use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;

final class RequiresSupervisorPoolConfigs
{
    public $poolConfigs;

    /** @param SupervisorPoolConfig[] $poolConfigs */
    public function __construct(array $poolConfigs) {
        $this->poolConfigs = $poolConfigs;
    }
}
