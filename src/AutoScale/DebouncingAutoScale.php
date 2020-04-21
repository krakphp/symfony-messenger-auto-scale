<?php

namespace Krak\SymfonyMessengerAutoScale\AutoScale;

use Krak\SymfonyMessengerAutoScale\AutoScale;

final class DebouncingAutoScale implements AutoScale
{
    const SCALE_UP = 'up';
    const SCALE_DOWN = 'down';

    private $autoScale;

    public function __construct(AutoScale $autoScale) {
        $this->autoScale = $autoScale;
    }

    public function __invoke(AutoScaleRequest $req): AutoScaleResponse {
        $resp = ($this->autoScale)($req);
        if ($req->timeSinceLastCall() === null || $resp->expectedNumProcs() === $req->numProcs() || $req->numProcs() === 0) {
            return $this->respWithDebounceSinceNeededScale($resp, null, null);
        }

        $scaleDirection = $resp->expectedNumProcs() > $req->numProcs() ? self::SCALE_UP : self::SCALE_DOWN;

        // number of seconds for a scale event to be active before allowing scale event
        $scaleThreshold = $scaleDirection === self::SCALE_UP
            ? $req->poolConfig()->scaleUpThresholdSeconds()
            : $req->poolConfig()->scaleDownThresholdSeconds();

        [$timeSinceNeededScale, $scaleDirectionSinceNeededScale] = $req->state()['debounce_since_needed_scale'] ?? [null, null];

        $debouncedResp = $resp->withExpectedNumProcs($req->numProcs());
        if ($timeSinceNeededScale === null || $scaleDirection !== $scaleDirectionSinceNeededScale) {
            return $this->respWithDebounceSinceNeededScale($debouncedResp, 0, $scaleDirection);
        }
        $updatedTimeSinceNeededScale = $timeSinceNeededScale + $req->timeSinceLastCall();
        if ($updatedTimeSinceNeededScale < $scaleThreshold) {
            return $this->respWithDebounceSinceNeededScale($debouncedResp, $updatedTimeSinceNeededScale, $scaleDirection);
        }

        return $this->respWithDebounceSinceNeededScale($resp, null, null);
    }

    private function respWithDebounceSinceNeededScale(AutoScaleResponse $resp, ?int $timeSinceNeededScale, ?string $scaleDirection): AutoScaleResponse {
        return $resp->withAddedState(['debounce_since_needed_scale' => [$timeSinceNeededScale, $scaleDirection]]);
    }
}
