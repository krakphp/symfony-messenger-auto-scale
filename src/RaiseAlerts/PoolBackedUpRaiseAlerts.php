<?php

namespace Krak\SymfonyMessengerAutoScale\RaiseAlerts;

use Krak\SymfonyMessengerAutoScale\AggregatingReceiverMessageCount;
use Krak\SymfonyMessengerAutoScale\Event\PoolBackedUpAlert;
use Krak\SymfonyMessengerAutoScale\RaiseAlerts;
use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;
use Psr\Container\ContainerInterface;

final class PoolBackedUpRaiseAlerts implements RaiseAlerts
{
    private $receiversById;

    public function __construct(ContainerInterface $receiversById) {
        $this->receiversById = $receiversById;
    }

    public function __invoke(SupervisorPoolConfig $poolConfig): iterable {
        $backedUpAlertThreshold = $poolConfig->poolConfig()->attributes()['backed_up_alert_threshold'] ?? null;
        if (!$backedUpAlertThreshold) {
            return [];
        }

        $getMessageCount = AggregatingReceiverMessageCount::createFromReceiverIds($poolConfig->receiverIds(), $this->receiversById);
        $total = $getMessageCount->getMessageCount();

        return $total > intval($backedUpAlertThreshold)
            ? [new PoolBackedUpAlert($poolConfig->name(), $backedUpAlertThreshold, $total)]
            : [];
    }
}
