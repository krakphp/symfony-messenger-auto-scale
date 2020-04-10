<?php

namespace Krak\SymfonyMessengerAutoScale;

interface ProcessManagerFactory
{
    public function createFromSupervisorPoolConfig(SupervisorPoolConfig $config): ProcessManager;
}
