<?php

namespace Krak\SymfonyMessengerAutoScale\Command\Pool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ResumeCommand extends PoolCommand
{
    protected function configure() {
        $this->setName('krak:auto-scale:pool:resume')
            ->setDescription('Request a resume for the selected pools')
            ->addPoolArgument('The names of the pools to perform a resume');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $poolNames = $this->getPoolNames($input);
        foreach ($poolNames as $poolName) {
            $control = $this->poolControlFactory->createForActor($poolName);
            $output->writeln("<info>Resuming Pool: {$poolName}</info>");
            $control->resume();
        }
    }
}
