<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\PoolControl;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\PoolStatus;
use PHPUnit\Framework\TestCase;

abstract class PoolControlTestOutline extends TestCase
{
    abstract public function createPoolControls(): array;

    /** @test */
    public function can_start_pool() {
        /** @var PoolControl\WorkerPoolControl $workerControl */
        $tup = $this->createPoolControls();
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::running());
        $workerPoolControl->scaleWorkers(0);

        $this->assertEquals(0, $workerPoolControl->getNumWorkers());
        $this->assertEquals(PoolStatus::running(), $workerPoolControl->getStatus());
        $this->assertEquals(false, $workerPoolControl->shouldStop());
        $this->assertEquals(null, $workerPoolControl->getPoolConfig());
        return $tup;
    }

    /**
     * @test
     * @depends can_start_pool
     */
    public function can_scale_workers(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->scaleWorkers(5);

        $this->assertEquals(5, $actorPoolControl->getNumWorkers());
        return $tup;
    }

    /**
     * @test
     * @depends can_scale_workers
     */
    public function can_update_pool_config(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $actorPoolControl->updatePoolConfig(new PoolConfig(5, 10, 50));

        $this->assertEquals(5, $workerPoolControl->getPoolConfig()->minProcs());
        $this->assertEquals(10, $workerPoolControl->getPoolConfig()->maxProcs());
        $this->assertEquals(50, $workerPoolControl->getPoolConfig()->messageRate());
        return $tup;
    }

    /**
     * @test
     * @depends can_update_pool_config
     */
    public function can_request_a_restart(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $actorPoolControl->restart();

        $this->assertEquals(true, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::running(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_request_a_restart
     */
    public function can_mark_consumer_stopping(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::stopping());

        $this->assertEquals(false, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::stopping(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_mark_consumer_stopping
     */
    public function can_finish_restart(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::running());

        $this->assertEquals(false, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::running(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_finish_restart
     */
    public function can_request_a_pause(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $actorPoolControl->pause();

        $this->assertEquals(true, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::running(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_request_a_pause
     */
    public function can_start_pausing_pool(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::stopping());

        $this->assertEquals(true, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::stopping(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_start_pausing_pool
     */
    public function can_pause_a_pool(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::stopped());

        $this->assertEquals(true, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::stopped(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_pause_a_pool
     */
    public function can_request_resume_a_pool(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $actorPoolControl->resume();

        $this->assertEquals(false, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::stopped(), $workerPoolControl->getStatus());
        return $tup;
    }

    /**
     * @test
     * @depends can_request_resume_a_pool
     */
    public function can_resume_a_pool(array $tup) {
        /** @var PoolControl\WorkerPoolControl */
        /** @var PoolControl\ActorPoolControl */
        [$workerPoolControl, $actorPoolControl] = $tup;

        $workerPoolControl->updateStatus(PoolStatus::running());

        $this->assertEquals(false, $workerPoolControl->shouldStop());
        $this->assertEquals(PoolStatus::running(), $workerPoolControl->getStatus());
        return $tup;
    }
}
