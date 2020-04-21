<?php

namespace Krak\SymfonyMessengerAutoScale\Command\Pool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PauseCommand extends PoolCommand
{
    protected function configure() {
        $this->setName('krak:auto-scale:pool:pause')
            ->setDescription('Request a pause for the selected pools')
            ->addPoolArgument('The names of the pools to perform a pause');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $poolNames = $this->getPoolNames($input);
        foreach ($poolNames as $poolName) {
            $control = $this->poolControlFactory->createForActor($poolName);
            $output->writeln("<info>Pausing Pool: {$poolName}</info>");
            $control->pause();
        }
    }
}
