<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature\Bundle;

use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MustMatchAllReceiversTest extends BaseBundleTestCase
{
    use InitsKernel;

    /** @var \Throwable|null $e */
    private $exception;
    private $configs;


    protected function setUp() {
        parent::setUp();
        $this->registerPublicServiceCompilerPass();
    }

    /** @test */
    public function throws_exception_if_not_all_receivers_are_matched() {
        $this->given_the_config_where_not_all_receivers_are_set_is_available();
        $this->when_the_kernel_is_booted();
        $this->then_the_not_matched_logic_exception_is_thrown();
    }

    /** @test */
    public function can_disable_must_match_all_receivers_flag() {
        $this->given_the_config_where_not_all_receivers_are_set_flag_is_disabled();
        $this->when_the_kernel_is_booted();
        $this->then_no_exception_is_thrown();
    }

    private function given_the_config_where_not_all_receivers_are_set_is_available() {
        $this->configs = [
            __DIR__ . '/../Fixtures/messenger-config.yaml',
            __DIR__ . '/../Fixtures/auto-scale-config-with-missing-receivers.yaml',
        ];
    }

    private function given_the_config_where_not_all_receivers_are_set_flag_is_disabled() {
        $this->configs = [
            __DIR__ . '/../Fixtures/messenger-config.yaml',
            __DIR__ . '/../Fixtures/auto-scale-config-with-missing-receivers-disabled.yaml'
        ];
    }

    private function when_the_kernel_is_booted() {
        try {
            $this->given_the_kernel_is_booted_with_config_resources($this->configs);
        } catch (\Throwable $e) {
            $this->exception = $e;
        }
    }

    private function then_the_not_matched_logic_exception_is_thrown() {
        $this->assertInstanceOf(\LogicException::class, $this->exception);
        $this->assertEquals('Some receivers were not matched by the pool config: catalog', $this->exception->getMessage());
    }

    private function then_no_exception_is_thrown() {
        $this->assertNull($this->exception);
    }
}
