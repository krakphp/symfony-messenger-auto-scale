<?php

namespace Krak\SymfonyMessengerAutoScale\Command;

use Krak\SymfonyMessengerAutoScale\RaiseAlerts;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AlertCommand extends Command
{
    private $raiseAlerts;
    private $eventDispatcher;
    private $supervisorPoolConfigs;

    public function __construct(RaiseAlerts $raiseAlerts, EventDispatcherInterface $eventDispatcher, array $supervisorPoolConfigs) {
        parent::__construct();
        $this->raiseAlerts = $raiseAlerts;
        $this->eventDispatcher = $eventDispatcher;
        $this->supervisorPoolConfigs = $supervisorPoolConfigs;
    }

    protected function configure() {
        $this->setName('krak:auto-scale:alert')
            ->setDescription('Raise any of the configured alerts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ($this->supervisorPoolConfigs as $poolConfig) {
            foreach (($this->raiseAlerts)($poolConfig) as $event) {
                $this->eventDispatcher->dispatch($event);
            }
        }
    }
}
