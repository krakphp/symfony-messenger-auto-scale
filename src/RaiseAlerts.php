<?php

namespace Krak\SymfonyMessengerAutoScale;

/** Takes the pool config and returns a yield-able set of event objects to be dispatched */
interface RaiseAlerts
{
    /** @return object[] */
    public function __invoke(SupervisorPoolConfig $poolConfig): iterable;
}
