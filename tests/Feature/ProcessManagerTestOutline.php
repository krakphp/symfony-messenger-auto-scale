<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\ProcessManager;
use PHPUnit\Framework\TestCase;

abstract class ProcessManagerTestOutline extends TestCase
{
    abstract public function createProcessManager(): ProcessManager;

    public static function setUpBeforeClass() {
        @unlink(__DIR__ . '/ProcessManager/Fixtures/run-proc.log');
    }

    public static function tearDownAfterClass() {
        @unlink(__DIR__ . '/ProcessManager/Fixtures/run-proc.log');
    }

    public function test_can_manage_procs() {
        $procManager = $this->createProcessManager();

        $proc1 = $procManager->createProcess();
        $proc2 = $procManager->createProcess();

        $this->assertEquals(true, $procManager->isProcessRunning($proc1));
        $this->assertEquals(true, $procManager->isProcessRunning($proc2));

        $proc1Pid = $procManager->getPid($proc1);
        $proc2Pid = $procManager->getPid($proc2);

        usleep(100000);

        $procManager->killProcess($proc1);
        $procManager->killProcess($proc2);
        $this->assertEquals(false, $procManager->isProcessRunning($proc1));
        $this->assertEquals(false, $procManager->isProcessRunning($proc2));

        $rows = array_unique(file(__DIR__ . '/ProcessManager/Fixtures/run-proc.log'));
        sort($rows);

        $this->assertEquals([
            "log: $proc1Pid\n",
            "log: $proc2Pid\n",
        ], $rows);
    }
}
