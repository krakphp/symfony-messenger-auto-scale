<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\AggregatingReceiverMessageCount;
use PHPUnit\Framework\TestCase;

final class AggregatingReceiverMessageCountTest extends TestCase
{
    /** @test */
    public function creation_from_receiver_locator() {
        $getMessageCount = AggregatingReceiverMessageCount::createFromReceiverIds(['a', 'b'], new ArrayContainer([
            'a' => new StaticGetMessageCount(5),
            'b' => new StaticGetMessageCount(4),
        ]));
        $this->assertEquals(9, $getMessageCount->getMessageCount());
    }
}
