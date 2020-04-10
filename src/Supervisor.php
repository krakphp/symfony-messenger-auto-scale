<?php

namespace Krak\SymfonyMessengerAutoScale;

use Psr\Log\{LoggerInterface, NullLogger};
use Psr\Container\ContainerInterface;

/**
 * Entrypoint for managing worker pools.
 */
final class Supervisor
{
    const SLEEP_TIME = 1;

    private $processManagerFactory;
    private $poolControlFactory;
    private $receiversById;
    private $supervisorPoolConfigs;
    private $autoScale;
    private $logger;
    private $shouldShutdown = false;

    /** @param SupervisorPoolConfig[] $supervisorPoolConfigs */
    public function __construct(ProcessManagerFactory $processManagerFactory, PoolControlFactory $poolControlFactory, ContainerInterface $receiversById, array $supervisorPoolConfigs, ?AutoScale $autoScale = null, ?LoggerInterface $logger = null) {
        $this->processManagerFactory = $processManagerFactory;
        $this->poolControlFactory = $poolControlFactory;
        $this->receiversById = $receiversById;
        $this->supervisorPoolConfigs = $this->assertUniquePoolNames($supervisorPoolConfigs);
        $this->autoScale = $autoScale ?: self::defaultAutoScale();
        $this->logger = new EventLogger($logger ?: new NullLogger());
    }

    public static function defaultAutoScale(): AutoScale {
        return new AutoScale\MinMaxClipAutoScale(new AutoScale\DebouncingAutoScale(new AutoScale\QueueSizeMessageRateAutoScale()));
    }

    public function run(): void {
        $this->registerPcntlSignalHandlers();

        $workerPools = $this->createWorkersFromPoolConfigs($this->supervisorPoolConfigs);
        $timeSinceLastCall = null;
        while (!$this->shouldShutdown) {
            foreach ($workerPools as $pool) {
                $pool->manage($timeSinceLastCall);
            }
            sleep(self::SLEEP_TIME);
            $timeSinceLastCall = self::SLEEP_TIME;
        }

        foreach ($workerPools as $pool) {
            $pool->stop();
        }
    }

    /**
     * @param SupervisorPoolConfig[] $supervisorPoolConfigs
     * @return SupervisorPoolConfig[]
     */
    private function assertUniquePoolNames(array $supervisorPoolConfigs): array {
        $poolNames = array_map(function(SupervisorPoolConfig $config) {
            return $config->name();
        }, $supervisorPoolConfigs);

        if (\count($poolNames) === count(\array_unique($poolNames))) {
            return $supervisorPoolConfigs;
        }

        throw new \RuntimeException('The pool names must be unique across all pool configurations.');
    }

    /** @return WorkerPool[] */
    private function createWorkersFromPoolConfigs(array $supervisorPoolConfigs): array {
        return array_map(function(SupervisorPoolConfig $config) {
            return new WorkerPool(
                $config->name(),
                AggregatingReceiverMessageCount::createFromReceiverIds($config->receiverIds(), $this->receiversById),
                $this->poolControlFactory->createForWorker($config->name()),
                $this->processManagerFactory->createFromSupervisorPoolConfig($config),
                $this->autoScale,
                $this->logger,
                $config->poolConfig()
            );
        }, $supervisorPoolConfigs);
    }

    private function registerPcntlSignalHandlers(): void {
        pcntl_async_signals(true);
        foreach ([SIGTERM, SIGINT] as $signal) {
            pcntl_signal($signal, function() {
                $this->shouldShutdown = true;
            });
        }
    }
}
