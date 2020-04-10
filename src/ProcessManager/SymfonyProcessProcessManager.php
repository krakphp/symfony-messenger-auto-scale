<?php

namespace Krak\SymfonyMessengerAutoScale\ProcessManager;

use Krak\SymfonyMessengerAutoScale\ProcessManager;
use Symfony\Component\Process\Process;

final class SymfonyProcessProcessManager implements ProcessManager
{
    private $cmd;

    public function __construct(array $cmd) {
        $this->cmd = $cmd;
    }

    public function createProcess() {
        $proc = new Process($this->cmd);
        $proc->setTimeout(null)
            ->disableOutput()
            ->start();
        return $proc;
    }

    public function killProcess($processRef) {
        /** @var Process $processRef */
        $processRef->stop();
    }

    public function isProcessRunning($processRef): bool {
        /** @var Process $processRef */
        return $processRef->isRunning();
    }

    public function getPid($processRef): ?int {
        /** @var Process $processRef */
        return $processRef->getPid();
    }
}
