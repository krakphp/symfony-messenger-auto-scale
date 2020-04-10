<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

final class StaticGetMessageCount implements ReceiverInterface, MessageCountAwareInterface
{
    public $messageCount;

    public function __construct(int $messageCount = 0) {
        $this->messageCount = $messageCount;
    }

    public function getMessageCount(): int {
        return $this->messageCount;
    }

    public function get(): iterable {
        return [];
    }

    public function ack(Envelope $envelope): void {
        // TODO: Implement ack() method.
    }

    public function reject(Envelope $envelope): void {
        // TODO: Implement reject() method.
    }
}
