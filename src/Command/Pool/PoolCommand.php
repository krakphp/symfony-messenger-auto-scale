<?php

namespace Krak\SymfonyMessengerAutoScale\Command\Pool;

use Krak\SymfonyMessengerAutoScale\PoolControlFactory;
use Krak\SymfonyMessengerAutoScale\SupervisorPoolConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class PoolCommand extends Command
{
    protected $poolControlFactory;
    protected $poolNames;

    public function __construct(PoolControlFactory $poolControlFactory, array $supervisorPoolConfigs) {
        $this->poolControlFactory = $poolControlFactory;
        $this->poolNames = array_map(function(SupervisorPoolConfig $config) {
            return $config->name();
        }, $supervisorPoolConfigs);

        parent::__construct();
    }

    protected function getPoolNames(InputInterface $input) {
        return $input->getArgument('names') ?: $this->poolNames;
    }

    /** @return $this */
    protected function addPoolArgument(string $description) {
        return $this->addArgument('names', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, $description . ', available options: ' . implode(', ', $this->poolNames));
    }
}
