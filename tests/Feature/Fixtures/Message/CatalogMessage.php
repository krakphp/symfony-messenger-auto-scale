<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message;

final class CatalogMessage
{
    public $id;

    public function __construct(int $id) {
        $this->id = $id;
    }
}
