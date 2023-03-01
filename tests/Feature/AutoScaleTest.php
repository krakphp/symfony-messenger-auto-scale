<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\AutoScale;
use Krak\SymfonyMessengerAutoScale\PoolConfig;
use PHPUnit\Framework\TestCase;

final class AutoScaleTest extends TestCase
{
    private $autoScale;
    private $poolConfig;
    /** @var AutoScale\AutoScaleResponse|null */
    private $autoScaleResp;
    private $autoScaleState;
    private $timeSinceLastCall;

    protected function setUp(): void {
        $this->poolConfig = new PoolConfig();
    }

    /**
     * @test
     * @dataProvider provide_for_min_max_boundaries
     */
    public function can_clip_to_min_max_boundaries(int $numProcs, int $expectedNumProcs) {
        $this->given_there_is_a_static_auto_scale_at($numProcs);
        $this->given_there_is_a_wrapping_min_max_auto_scale();
        $this->given_the_pool_config_has_min_max(1, 5);
        $this->when_auto_scale_occurs();
        $this->then_expected_num_procs_is($expectedNumProcs);
    }

    public function provide_for_min_max_boundaries() {
        yield 'below min' => [0, 1];
        yield 'ok' => [3, 3];
        yield 'above max' => [6, 5];
    }

    /**
     * @test
     * @dataProvider provide_for_queue_size_and_message_rate
     */
    public function can_determine_num_procs_on_queue_size_and_message_rate(int $queueSize, int $messageRate, int $expectedNumProcs) {
        $this->given_there_is_a_queue_size_threshold_auto_scale();
        $this->given_the_pool_config_has_message_rate($messageRate);
        $this->when_auto_scale_occurs($queueSize);
        $this->then_expected_num_procs_is($expectedNumProcs);
    }

    public function provide_for_queue_size_and_message_rate() {
        yield 'queue@0,100' => [0, 100, 0];
        yield 'queue@50,100' => [50, 100, 1];
        yield 'queue@100,100' => [100, 100, 1];
        yield 'queue@101,100' => [101, 100, 2];
        yield 'queue@200,100' => [200, 100, 2];
        yield 'queue@201,100' => [201, 100, 3];
        yield 'queue@53,5' => [53, 5, 11];
        yield 'queue@57,5' => [57, 5, 12];
    }

    /**
     * @test
     * @dataProvider provide_for_debouncing
     *
     * @param array<array{int, int}>> $runs array of tuples where first element is queueSize and next element is current num procs
     */
    public function debounces_auto_scaling(array $runs, int $expectedProcs) {
        $this->given_there_is_a_queue_size_threshold_auto_scale();
        $this->given_there_is_a_wrapping_debouncing_auto_scale();
        $this->given_the_pool_config_has_message_rate(1);
        $this->given_the_time_since_last_call_is(1);
        $this->given_the_pool_config_has_scale_up_threshold_of(2);
        $this->given_the_pool_config_has_scale_down_threshold_of(4);
        $this->when_auto_scale_occurs_n_times($runs);
        $this->then_expected_num_procs_is($expectedProcs);
    }

    public function provide_for_debouncing() {
        yield 'no debouncing on first auto scale up' => [[
           [2, 0]
        ], 2];
        yield 'debounces scale up before threshold is met' => [[
            [1, 0],
            [1, 1],
            [2, 1],
            [2, 1],
        ], 1];
        yield 'debounces scale up until threshold is met' => [[
            [1, 0],
            [1, 1],
            [2, 1],
            [3, 1],
            [2, 1],
        ], 2];
        yield 'resets debounce state if expected num procs matches current procs' => [[
            [1, 0],
            [1, 1],
            [2, 1],
            [2, 1],
            [1, 1],
            [2, 1],
        ], 1];
        yield 'resets debounce state if needed scale direction changes' => [[
            [1, 0],
            [1, 1],
            [2, 1],
            [2, 1],
            [0, 1],
            [2, 1],
        ], 1];
        yield 'prevents scale up debounce if scaling from 0' => [[
            [0, 0],
            [0, 0],
            [4, 0]
        ], 4];
        yield 'resets threshold after first scale event' => [[
            [1, 0],
            [2, 1],
            [2, 1],
            [2, 1],
            [3, 2],
            [3, 2],
        ], 2];
        yield 'multiple scale up events' => [[
            [1, 0],
            [2, 1],
            [2, 1],
            [2, 1],
            [3, 2],
            [3, 2],
            [3, 2],
        ], 3];
        yield 'debounces on scale down' => [[
            [2, 0],
            [0, 2],
        ], 2];
        yield 'finishes debounces on scale down' => [[
            [2, 0],
            [0, 2],
            [0, 2],
            [0, 2],
            [0, 2],
            [0, 2],
        ], 0];
    }

    private function given_there_is_a_static_auto_scale_at(int $numProcs): void {
        $this->autoScale = new class($numProcs) implements AutoScale {
            private $expectedNumProcs;

            public function __construct(int $expectedNumProcs) {
                $this->expectedNumProcs = $expectedNumProcs;
            }

            public function __invoke(AutoScale\AutoScaleRequest $req): AutoScale\AutoScaleResponse {
                return new AutoScale\AutoScaleResponse($req->state(), $this->expectedNumProcs);
            }
        };
    }

    private function given_there_is_a_queue_size_threshold_auto_scale(): void {
        $this->autoScale = new AutoScale\QueueSizeMessageRateAutoScale();
    }

    private function given_there_is_a_wrapping_min_max_auto_scale(): void {
        $this->autoScale = new AutoScale\MinMaxClipAutoScale($this->autoScale);
    }

    private function given_there_is_a_wrapping_debouncing_auto_scale(): void {
        $this->autoScale = new AutoScale\DebouncingAutoScale($this->autoScale);
    }

    private function given_the_pool_config_has_min_max(?int $min, ?int $max): void {
        $this->poolConfig = $this->poolConfig->withMinMax($min, $max);
    }

    private function given_the_pool_config_has_message_rate(int $messageRate): void {
        $this->poolConfig = $this->poolConfig->withMessageRate($messageRate);
    }

    private function given_the_pool_config_has_scale_up_threshold_of(int $threshold) {
        $this->poolConfig = $this->poolConfig->withScaleUpThresholdSeconds($threshold);
    }

    private function given_the_pool_config_has_scale_down_threshold_of(int $threshold) {
        $this->poolConfig = $this->poolConfig->withScaleDownThresholdSeconds($threshold);
    }

    private function given_the_time_since_last_call_is(?int $timeSinceLastCall) {
        $this->timeSinceLastCall = $timeSinceLastCall;
    }

    private function when_auto_scale_occurs(int $queueSize = 1, int $numProcs = 1) {
        $this->autoScaleResp = ($this->autoScale)(new AutoScale\AutoScaleRequest($this->autoScaleState, $this->timeSinceLastCall, $numProcs, $queueSize, $this->poolConfig));
        $this->autoScaleState = $this->autoScaleResp->state();
    }

    private function when_auto_scale_occurs_n_times(array $args) {
        foreach ($args as [$queueSize, $numProcs]) {
            $this->when_auto_scale_occurs($queueSize, $numProcs);
        }
    }

    private function then_expected_num_procs_is(int $expected) {
        $this->assertEquals($expected, $this->autoScaleResp->expectedNumProcs());
    }
}
