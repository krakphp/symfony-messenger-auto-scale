<?php

namespace Krak\SymfonyMessengerAutoScale;

final class PoolStatus
{
    private $value;

    private function __construct(string $value) {
        $this->value = $value;
    }

    public static function running(): self {
        return new self('running');
    }

    public static function stopped(): self {
        return new self('stopped');
    }

    public static function paused(): self {
        return new self('paused');
    }

    public static function stopping(): self {
        return new self('stopping');
    }

    public function value(): string {
        return $this->value;
    }

    public function __toString() {
        return $this->value();
    }
}
