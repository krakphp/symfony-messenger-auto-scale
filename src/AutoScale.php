<?php

namespace Krak\SymfonyMessengerAutoScale;

use Krak\SymfonyMessengerAutoScale\AutoScale\AutoScaleRequest;
use Krak\SymfonyMessengerAutoScale\AutoScale\AutoScaleResponse;

/** Service responsible for determining the appropriate size of the pool based off of current state of pool and config */
interface AutoScale
{
    public function __invoke(AutoScaleRequest $req): AutoScaleResponse;
}
