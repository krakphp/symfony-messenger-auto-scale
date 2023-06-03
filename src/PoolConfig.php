<?php

namespace Krak\SymfonyMessengerAutoScale;

final class PoolConfig implements \JsonSerializable
{
    private $minProcs;
    private $maxProcs;
    /** Max number of messages allowed for a single proc */
    private $messageRate;
    /** The time it takes in seconds before we start scaling up over provisioned procs */
    private $scaleUpThresholdSeconds;
    /** The time it takes in seconds before we start scaling down over provisioned procs */
    private $scaleDownThresholdSeconds;
    private $attributes;

    public function __construct(?int $minProcs = null, ?int $maxProcs = null, int $messageRate = 100, int $scaleUpThresholdSeconds = 5, int $scaleDownThresholdSeconds = 60, array $attributes = []) {
        $this->minProcs = $minProcs;
        $this->maxProcs = $maxProcs;
        $this->messageRate = $messageRate;
        $this->scaleUpThresholdSeconds = $scaleUpThresholdSeconds;
        $this->scaleDownThresholdSeconds = $scaleDownThresholdSeconds;
        $this->attributes = $attributes;
    }

    public static function createFromOptionsArray(array $poolConfig): self {
        return new self(
            $poolConfig['min_procs'] ?? null,
            $poolConfig['max_procs'] ?? null,
            $poolConfig['message_rate'] ?? 100,
            $poolConfig['scale_up_threshold_seconds'] ?? 5,
            $poolConfig['scale_down_threshold_seconds'] ?? 60,
            $poolConfig
        );
    }

    public function minProcs(): ?int {
        return $this->minProcs;
    }

    public function maxProcs(): ?int {
        return $this->maxProcs;
    }

    public function withMinMax(?int $minProcs, ?int $maxProcs): self {
        $self = clone $this;
        $self->minProcs = $minProcs;
        $self->maxProcs = $maxProcs;
        return $self;
    }

    public function messageRate(): int {
        return $this->messageRate;
    }
    
    public function withMessageRate(int $messageRate): self {
        $self = clone $this;
        $self->messageRate = $messageRate;
        return $self;
    }
    
    public function scaleUpThresholdSeconds(): int {
        return $this->scaleUpThresholdSeconds;
    }
    
    public function withScaleUpThresholdSeconds(int $scaleUpThresholdSeconds): self {
        $self = clone $this;
        $self->scaleUpThresholdSeconds = $scaleUpThresholdSeconds;
        return $self;
    }

    public function scaleDownThresholdSeconds(): int {
        return $this->scaleDownThresholdSeconds;
    }
    
    public function withScaleDownThresholdSeconds( $scaleDownThresholdSeconds): self {
        $self = clone $this;
        $self->scaleDownThresholdSeconds = $scaleDownThresholdSeconds;
        return $self;
    }

    public function attributes(): array {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed {
        return get_object_vars($this);
    }
}
