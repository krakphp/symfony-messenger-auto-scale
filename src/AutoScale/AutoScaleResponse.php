<?php

namespace Krak\SymfonyMessengerAutoScale\AutoScale;

final class AutoScaleResponse
{
    private $state;
    private $expectedNumProcs;

    public function __construct(?array $state, int $expectedNumProcs) {
        if ($expectedNumProcs < 0) {
            throw new \RuntimeException('Expected number of procs must be zero or greater.');
        }
        $this->state = $state;
        $this->expectedNumProcs = $expectedNumProcs;
    }

    public function state(): ?array {
        return $this->state;
    }

    public function withState(?array $state): self {
        $self = clone $this;
        $self->state = $state;
        return $self;
    }

    public function withAddedState(array $stateToMerge): self {
        return $this->withState(array_merge($this->state ?? [], $stateToMerge));
    }

    public function expectedNumProcs(): int {
        return $this->expectedNumProcs;
    }

    public function withExpectedNumProcs(int $expectedNumProcs): self {
        $self = clone $this;
        $self->expectedNumProcs = $expectedNumProcs;
        return $self;
    }
}
