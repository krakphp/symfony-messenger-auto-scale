<?php

namespace Krak\SymfonyMessengerAutoScale;

use Krak\SymfonyMessengerAutoScale\AutoScale\AutoScaleRequest;
use Krak\SymfonyMessengerAutoScale\PoolControl\WorkerPoolControl;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/**
 * Represents a collection of worker processes that are scaled/managed
 * according to the pool config and the size of the combined queue for all the receivers.
 */
final class WorkerPool
{
    const DEFAULT_HEARTBEAT_INTERVAL = 60;

    private $name;
    private $getMessageCount;
    private $poolControl;
    private $processManager;
    private $autoScale;
    private $logger;
    private $poolConfig;
    private $procs;
    private $autoScaleState;
    private $timeSinceLastHeartBeat = 0;

    public function __construct(
        string $name,
        MessageCountAwareInterface $getMessageCount,
        WorkerPoolControl $poolControl,
        ProcessManager $processManager,
        AutoScale $autoScale,
        EventLogger $logger,
        PoolConfig $poolConfig
    ) {
        $this->name = $name;
        $this->getMessageCount = $getMessageCount;
        $this->poolControl = $poolControl;
        $this->processManager = $processManager;
        $this->autoScale = $autoScale;
        $this->logger = $logger;
        $this->poolConfig = $poolConfig;
        $this->procs = [];
    }

    public function manage(?int $timeSinceLastCallInSeconds): void {
        $poolConfig = $this->poolControl->getPoolConfig() ?: $this->poolConfig;
        $sizeOfQueues = $this->getMessageCount->getMessageCount();

        if ($this->poolControl->shouldStop()) {
            $this->stop();
            return;
        }

        $this->beatHeart($poolConfig, $sizeOfQueues, $timeSinceLastCallInSeconds);
        $this->refreshDeadProcs();

        $resp = ($this->autoScale)(new AutoScaleRequest($this->autoScaleState, $timeSinceLastCallInSeconds, $this->numProcs(), $sizeOfQueues, $poolConfig));
        $this->scaleTo($resp->expectedNumProcs());
        $this->autoScaleState = $resp->state();
    }

    public function stop(): void {
        if ($this->poolControl->getStatus() == PoolStatus::stopped() && $this->numProcs() == 0) {
            return;
        }

        $this->logEvent('Stopping Pool', 'stopping');
        $this->poolControl->updateStatus(PoolStatus::stopping());

        $this->scaleTo(0);

        $this->logger->logEvent('Pool stopped', 'stopped');
        $this->poolControl->updateStatus(PoolStatus::stopped());
    }

    private function beatHeart(PoolConfig $poolConfig, int $sizeOfQueues, ?int $timeSinceLastCallInSeconds): void {
        $heartBeatInterval = $poolConfig->attributes()['heartbeat_interval'] ?? self::DEFAULT_HEARTBEAT_INTERVAL;
        $this->timeSinceLastHeartBeat += $timeSinceLastCallInSeconds ?: 0;

        if ($this->timeSinceLastHeartBeat >= $heartBeatInterval) {
            $this->timeSinceLastHeartBeat = 0;
        }

        if ($this->timeSinceLastHeartBeat !== 0) {
            return;
        }

        $this->poolControl->updateStatus(PoolStatus::running(), $sizeOfQueues);
        $this->logEvent('Running', 'running', ['sizeOfQueues' => $sizeOfQueues]);
    }

    /** Scales up or down to the expected num procs */
    private function scaleTo(int $expectedNumProcs): void {
        while ($expectedNumProcs > $this->numProcs()) {
            $this->scaleUp();
        }
        while ($expectedNumProcs < $this->numProcs()) {
            $this->scaleDown();
        }
    }

    private function scaleDown() {
        $procRef = array_pop($this->procs);
        $this->processManager->killProcess($procRef);
        $this->logEvent("Scaling down worker pool", 'scale', ['direction' => 'down']);
        $this->poolControl->scaleWorkers($this->numProcs());
    }

    private function scaleUp() {
        $proc = $this->processManager->createProcess();
        $this->procs[] = $proc;
        $this->logEvent("Scaling up worker pool", 'scale', ['direction' => 'up']);
        $this->poolControl->scaleWorkers($this->numProcs());
    }

    private function logEvent(string $message, string $event, array $context = []): void {
        $this->logger->logEvent($message, 'pool_'.$event, array_merge([
            'num_procs' => $this->numProcs(),
            'pool' => $this->name,
        ], $context));
    }

    private function numProcs(): int {
        return count($this->procs);
    }

    /** if any of our procs got killed for some reason, we'll need to start up a replacement proc */
    private function refreshDeadProcs() {
        $this->procs = \iterator_to_array((function(array $procs) {
            foreach ($procs as $proc) {
                if ($this->processManager->isProcessRunning($proc)) {
                    yield $proc;
                    continue;
                }

                $this->logEvent('Restarting Process', 'restart_proc', [
                    'pid' => $this->processManager->getPid($proc),
                ]);
                $this->processManager->killProcess($proc);
                yield $this->processManager->createProcess();
            }
        })($this->procs));
    }
}
