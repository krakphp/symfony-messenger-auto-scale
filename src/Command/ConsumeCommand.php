<?php

namespace Krak\SymfonyMessengerAutoScale\Command;

use Krak\SymfonyMessengerAutoScale\Supervisor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumeCommand extends Command
{
    private $supervisor;

    public function __construct(Supervisor $supervisor) {
        parent::__construct();
        $this->supervisor = $supervisor;
    }

    protected function configure() {
        $this->setName('krak:auto-scale:consume')
            ->setDescription('Start the supervisor to manage the worker consumers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('<info>Starting Supervisor.</info>');
        $this->supervisor->run();
    }
}
