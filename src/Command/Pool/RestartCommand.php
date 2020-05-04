<?php

namespace Krak\SymfonyMessengerAutoScale\Command\Pool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RestartCommand extends PoolCommand
{
    protected function configure() {
        $this->setName('krak:auto-scale:pool:restart')
            ->setDescription('Request a restart for the selected pools')
            ->addPoolArgument('The names of the pools to perform a restart');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $poolNames = $this->getPoolNames($input);
        foreach ($poolNames as $poolName) {
            $control = $this->poolControlFactory->createForActor($poolName);
            $output->writeln("<info>Restarting Pool: {$poolName}</info>");
            $control->restart();
        }

        return 0;
    }
}
