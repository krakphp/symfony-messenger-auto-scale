<?php

namespace Krak\SymfonyMessengerAutoScale\RaiseAlerts;

use Krak\SymfonyMessengerAutoScale\RaiseAlerts;
use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;

final class ChainRaiseAlerts implements RaiseAlerts
{
    private $raiseAlerts;

    /** @param RaiseAlerts[] $raiseAlerts */
    public function __construct(iterable $raiseAlerts) {
        $this->raiseAlerts = $raiseAlerts;
    }

    public function __invoke(SupervisorPoolConfig $poolConfig): iterable {
        foreach ($this->raiseAlerts as $raiseAlert) {
            yield from $raiseAlert($poolConfig);
        }
    }
}
