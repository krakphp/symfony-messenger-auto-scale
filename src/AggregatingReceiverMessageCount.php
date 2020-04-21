<?php

namespace Krak\SymfonyMessengerAutoScale;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/** take a collection of receivers and return the total count of pending messages */
final class AggregatingReceiverMessageCount implements MessageCountAwareInterface
{
    private $receivers;

    public function __construct(ReceiverInterface ...$receivers) {
        $this->receivers = $receivers;
    }

    public static function createFromReceiverIds(array $receiverIds, ContainerInterface $receiversById) {
        return new self(...array_map(function(string $receverId) use ($receiversById) {
            return $receiversById->get($receverId);
        }, $receiverIds));
    }

    public function getMessageCount(): int {
        return array_reduce($this->receivers, function(int $sum, ReceiverInterface $receiver) {
            return $receiver instanceof MessageCountAwareInterface ? $receiver->getMessageCount() + $sum : $sum;
        }, 0);
    }
}
