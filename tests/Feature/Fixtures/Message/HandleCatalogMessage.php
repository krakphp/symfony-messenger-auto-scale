<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message;

final class HandleCatalogMessage
{
    public function __invoke(CatalogMessage $message): void {
        file_put_contents(__DIR__ . '/../_message-info.txt', 'catalog: ' . $message->id . "\n", FILE_APPEND);
    }
}
