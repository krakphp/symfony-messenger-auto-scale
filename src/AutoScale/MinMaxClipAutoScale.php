<?php

namespace Krak\SymfonyMessengerAutoScale\AutoScale;

use Krak\SymfonyMessengerAutoScale\AutoScale;

final class MinMaxClipAutoScale implements AutoScale
{
    private $autoScale;

    public function __construct(AutoScale $autoScale) {
        $this->autoScale = $autoScale;
    }

    public function __invoke(AutoScaleRequest $req): AutoScaleResponse {
        $resp = ($this->autoScale)($req);
        $poolConfig = $req->poolConfig();
        $expectedNumProcs = min($poolConfig->maxProcs(), max($resp->expectedNumProcs(), $poolConfig->minProcs()));
        return $resp->withExpectedNumProcs($expectedNumProcs);
    }
}
