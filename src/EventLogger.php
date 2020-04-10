<?php

namespace Krak\SymfonyMessengerAutoScale;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Log structured messages/events throughout the system to support building UIs with logging visualization tools like
 * Kibana.
 */
final class EventLogger extends AbstractLogger
{
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function logEvent(string $message, string $event, array $context = []): void {
        $this->log(LogLevel::INFO, $message, [
           'messenger_auto_scale_event' => $event,
           'messenger_auto_scale_event_' . $event . '_context' => $context,
        ]);
    }

    public function log($level, $message, array $context = array()) {
        $this->logger->log($level, $message, $context);
    }
}
