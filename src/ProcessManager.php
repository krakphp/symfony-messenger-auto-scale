<?php

namespace Krak\SymfonyMessengerAutoScale;

use Psr\Log\LoggerInterface;

interface ProcessManager
{
    /** @return mixed a process ref */
    public function createProcess();
    public function killProcess($processRef);
    public function isProcessRunning($processRef): bool;
    public function getPid($processRef): ?int;
}
