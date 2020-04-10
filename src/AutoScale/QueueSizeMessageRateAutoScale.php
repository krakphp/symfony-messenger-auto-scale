<?php

namespace Krak\SymfonyMessengerAutoScale\AutoScale;

use Krak\SymfonyMessengerAutoScale\AutoScale;

final class QueueSizeMessageRateAutoScale implements AutoScale
{
    public function __invoke(AutoScaleRequest $req): AutoScaleResponse {
        $expectedNumProcs = ceil($req->sizeOfQueue() / $req->poolConfig()->messageRate());
        return new AutoScaleResponse($req->state(), $expectedNumProcs);
    }
}
