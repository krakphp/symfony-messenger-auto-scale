<?php

namespace Krak\SymfonyMessengerAutoScale\Command\Pool;

use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class StatusCommand extends PoolCommand
{
    protected function configure() {
        $this->setName('krak:auto-scale:pool:status')
            ->setDescription('Show the status of the selected pool (or all if no pool name is given).')
            ->addPoolArgument('The names of the pools to display the status')
            ->addOption('poll', 'p', InputOption::VALUE_NONE, 'Poll the pool control indefinitely at an interval')
            ->addOption('poll-interval', 'i', InputOption::VALUE_REQUIRED, 'The interval to poll at, defaults to 5 seconds', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $poolNames = $this->getPoolNames($input);
        $shouldPoll = $input->getOption('poll');
        $pollInterval = $input->getOption('poll-interval');

        if (!$shouldPoll) {
            $this->printPools($output, $poolNames);
            return 0;
        }

        while (true) {
            $this->printPools($output, $poolNames);
            sleep($pollInterval);
        }

        return 0;
    }

    private function printPools(OutputInterface $output, array $poolNames) {
        $table = new Table($output);
        $table->setHeaders(['Pool', 'Size of Queues', 'Num Workers', 'Status', 'Should Stop', 'Config']);
        foreach ($poolNames as $poolName) {
            $poolControl = $this->poolControlFactory->createForActor($poolName);
            $table->addRow([$poolName, $poolControl->getSizeOfQueues(), $poolControl->getNumWorkers(), (string) $poolControl->getStatus(), $poolControl->shouldStop(), $this->encodePoolConfig($poolControl->getPoolConfig())]);
        }
        $table->render();
    }

    private function encodePoolConfig(?PoolConfig $poolConfig): string {
        if (\function_exists('json_encode')) {
            return \json_encode($poolConfig);
        }

        return print_r($poolConfig->jsonSerialize(), true);
    }
}
