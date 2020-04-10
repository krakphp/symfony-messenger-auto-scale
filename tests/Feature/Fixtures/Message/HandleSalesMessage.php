<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message;

final class HandleSalesMessage
{
    public function __invoke(SalesMessage $message) {
        file_put_contents(__DIR__ . '/../_message-info.txt', 'sales: ' . $message->id . "\n", FILE_APPEND);
    }
}
