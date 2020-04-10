<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\AutoScale\DebouncingAutoScale;
use Krak\SymfonyMessengerAutoScale\AutoScale\QueueSizeMessageRateAutoScale;
use Krak\SymfonyMessengerAutoScale\EventLogger;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use Krak\SymfonyMessengerAutoScale\PoolControl\InMemoryPoolControl;
use Krak\SymfonyMessengerAutoScale\PoolStatus;
use Krak\SymfonyMessengerAutoScale\ProcessManager\MockProcessManager;
use Krak\SymfonyMessengerAutoScale\WorkerPool;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

final class WorkerPoolTest extends TestCase
{
    private $getMessageCount;
    private $procManager;
    private $testLogger;
    private $logger;
    private $autoScale;
    private $poolControl;
    /** @var WorkerPool */
    private $workerPool;

    protected function setUp() {
        $this->getMessageCount = new StaticGetMessageCount();
        $this->procManager = new MockProcessManager();
        $this->testLogger = new TestLogger();
        $this->logger = new EventLogger($this->testLogger);
        $this->poolControl = new InMemoryPoolControl();
    }

    /** @test */
    public function can_start_a_worker_pool() {
        $this->given_the_unprocessed_message_count_is(5);
        $this->given_there_is_a_queue_size_auto_scale();
        $this->given_the_worker_pool_is_created();
        $this->when_the_worker_pool_is_managed();
        $this->then_the_total_running_procs_is(5);
        $this->then_the_pool_control_status_is(PoolStatus::running());
    }

    /** @test */
    public function restarting_a_worker_pool_stops_on_first_manage() {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5);
        $this->given_the_pool_control_requests_a_restart();
        $this->when_the_worker_pool_is_managed();
        $this->then_the_total_running_procs_is(0);
        $this->then_the_pool_control_status_is(PoolStatus::stopped());
    }

    /** @test */
    public function restarting_a_worker_pool_restarts_on_second_manage() {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5);
        $this->given_the_pool_control_requests_a_restart();
        $this->when_the_worker_pool_is_managed_n_times([null, null]);
        $this->then_the_total_running_procs_is(5);
        $this->then_the_pool_control_status_is(PoolStatus::running());
    }

    /** @test */
    public function stopping_a_worker_pool() {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5);
        $this->when_the_worker_pool_is_stopped();
        $this->then_the_total_running_procs_is(0);
        $this->then_the_pool_control_status_is(PoolStatus::stopped());
    }

    /** @test */
    public function refreshing_dead_procs() {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5);
        $this->given_a_proc_is_killed(3);
        $this->when_the_worker_pool_is_managed();
        $this->then_the_total_running_procs_is(5);
    }

    /**
     * @test
     * @dataProvider provide_for_scaling_procs
     */
    public function can_scale_procs_to_meet_autoscale_expectations(int $expectedNumProcs) {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5);
        $this->given_the_unprocessed_message_count_is($expectedNumProcs);
        $this->when_the_worker_pool_is_managed();
        $this->then_the_total_running_procs_is($expectedNumProcs);
    }

    public function provide_for_scaling_procs() {
        yield 'scale up' => [6];
        yield 'scale down' => [5];
    }

    /** @test */
    public function can_maintain_state_to_auto_scale() {
        $this->given_there_is_a_running_worker_pool_with_num_procs(5, function() {
            $this->given_there_is_a_queue_size_auto_scale();
            $this->given_there_is_a_wrapping_debouncing_auto_scale();
        });
        $this->given_the_unprocessed_message_count_is(6);
        $this->when_the_worker_pool_is_managed_n_times([1, 1]);
        $this->then_the_total_running_procs_is(5);
    }

    private function given_the_unprocessed_message_count_is(int $messageCount) {
        $this->getMessageCount->messageCount = $messageCount;
    }

    private function given_the_worker_pool_is_created(): void {
        $this->workerPool = new WorkerPool(
            'test',
            $this->getMessageCount,
            $this->poolControl,
            $this->procManager,
            $this->autoScale,
            $this->logger,
            (new PoolConfig())->withMessageRate(1)->withScaleUpThresholdSeconds(5)
        );
    }

    private function given_there_is_a_running_worker_pool_with_num_procs(int $numProcs = 5, ?callable $initAutoScale = null) {
        $this->given_the_unprocessed_message_count_is($numProcs);
        ($initAutoScale ?? function() {
            $this->given_there_is_a_queue_size_auto_scale();
        })();
        $this->given_the_worker_pool_is_created();
        $this->when_the_worker_pool_is_managed();
    }

    private function given_there_is_a_queue_size_auto_scale() {
        $this->autoScale = new QueueSizeMessageRateAutoScale();
    }

    private function given_there_is_a_wrapping_debouncing_auto_scale(): void {
        $this->autoScale = new DebouncingAutoScale($this->autoScale);
    }

    private function given_the_pool_control_requests_a_restart() {
        $this->poolControl->restart();
    }

    private function given_a_proc_is_killed(int $procId) {
        $this->procManager->stopProcess($procId);
    }

    private function given_the_worker_pool_has_been_managed_n_times(array $args): void {
        $this->when_the_worker_pool_is_managed_n_times($args);
    }

    private function when_the_worker_pool_is_stopped() {
        $this->workerPool->stop();
    }

    private function when_the_worker_pool_is_managed(?int $timeSinceLastCallSeconds = null) {
        $this->workerPool->manage($timeSinceLastCallSeconds);
    }

    private function when_the_worker_pool_is_managed_n_times(array $args): void {
        foreach ($args as $timeSinceLastCallSeconds) {
            $this->workerPool->manage($timeSinceLastCallSeconds);
        }
    }

    private function then_the_total_running_procs_is(int $numProcs): void {
        $this->assertCount(
            $numProcs,
            array_filter($this->procManager->getProcs(), function(array $proc) { return $proc['isRunning']; })
        );
        $this->assertEquals($numProcs, $this->poolControl->getNumWorkers());
    }

    private function then_the_pool_control_status_is(PoolStatus $status): void {
        $this->assertEquals($status, $this->poolControl->getStatus());
    }
}
