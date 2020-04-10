<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message;

final class SalesMessage
{
    public $id;

    public function __construct(int $id) {
        $this->id = $id;
    }
}
