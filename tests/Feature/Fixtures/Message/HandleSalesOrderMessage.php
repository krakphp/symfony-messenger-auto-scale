<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message;

final class HandleSalesOrderMessage
{
    public function __invoke(SalesMessage $message) {
        file_put_contents(__DIR__ . '/../_message-info.txt', 'sales-order: ' . $message->id . "\n", FILE_APPEND);
    }
}
