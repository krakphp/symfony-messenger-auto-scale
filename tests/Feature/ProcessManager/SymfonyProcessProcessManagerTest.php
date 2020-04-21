<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\ProcessManager;

use Krak\SymfonyMessengerAutoScale\ProcessManager;
use Krak\SymfonyMessengerAutoScale\Tests\Feature\ProcessManagerTestOutline;

final class SymfonyProcessProcessManagerTest extends ProcessManagerTestOutline
{
    public function createProcessManager(): ProcessManager {
        return new ProcessManager\SymfonyProcessProcessManager(['php', __DIR__ . '/Fixtures/run-proc.php']);
    }
}
