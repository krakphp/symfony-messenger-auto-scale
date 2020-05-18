<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Feature;

use Krak\SymfonyMessengerAutoScale\MessengerAutoScaleBundle;
use Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\RequiresSupervisorPoolConfigs;
use Krak\SymfonyMessengerRedis\MessengerRedisBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

final class BundleTest extends BaseBundleTestCase
{
    /** @var RequiresSupervisorPoolConfigs */
    private $requiresPoolConfigs;
    private $proc;

    protected function setUp() {
        parent::setUp();
        $this->addCompilerPass(new PublicServicePass('/(Krak.*|krak\..*|messenger.default_serializer|message_bus)/'));
    }

    protected function tearDown() {
        if ($this->proc) {
            $this->proc->stop();
        }
    }

    protected function getBundleClass() {
        return MessengerAutoScaleBundle::class;
    }

    /** @test */
    public function supervisor_pool_config_is_built_from_sf_configuration() {
        $this->given_the_kernel_is_booted_with_messenger_and_auto_scale_config();
        $this->when_the_requires_supervisor_pool_configs_is_created();
        $this->then_the_pool_configs_match_the_auto_scale_config();
    }

    /** @test */
    public function receiver_to_pool_mapping_is_built_from_auto_scale_config() {
        $this->given_the_kernel_is_booted_with_messenger_and_auto_scale_config();
        $this->when_the_requires_supervisor_pool_configs_is_created();
        $this->then_the_receiver_to_pools_mapping_matches([
            'catalog' => 'default',
            'sales' => 'sales',
            'sales_order' => 'sales',
        ]);
    }

    /** @test */
    public function consuming_messages_with_a_running_supervisor() {
        $this->given_the_message_info_file_is_reset();
        $this->given_the_kernel_is_booted_with_messenger_and_auto_scale_config();
        $this->given_the_supervisor_is_started();
        $this->when_the_messages_are_dispatched();
        $this->then_the_message_info_file_matches_the_messages_sent();
    }

    public function alerts_system() {
        // setup a queue that's overflowing
        // run the queue command
    }

    private function given_the_message_info_file_is_reset() {
        @unlink(__DIR__ . '/Fixtures/_message-info.txt');
    }

    private function given_the_kernel_is_booted_with_messenger_and_auto_scale_config() {
        $kernel = $this->createKernel();
        $kernel->addBundle(Fixtures\TestFixtureBundle::class);
        $kernel->addBundle(MessengerRedisBundle::class);
        $kernel->addConfigFile(__DIR__ . '/Fixtures/messenger-config.yaml');
        $kernel->addConfigFile(__DIR__ . '/Fixtures/auto-scale-config.yaml');
        $this->bootKernel();
    }

    private function given_the_supervisor_is_started() {
        $this->proc = new Process([__DIR__ . '/Fixtures/console', 'krak:auto-scale:consume']);
        $this->proc
            ->setTimeout(null)
            ->disableOutput()
            ->start();
    }

    private function when_the_requires_supervisor_pool_configs_is_created(): void {
        $this->requiresPoolConfigs = $this->getContainer()->get(RequiresSupervisorPoolConfigs::class);
    }

    private function when_the_messages_are_dispatched() {
        /** @var MessageBusInterface $bus */
        $bus = $this->getContainer()->get('message_bus');
        $bus->dispatch(new Fixtures\Message\CatalogMessage(1));
        $bus->dispatch(new Fixtures\Message\SalesMessage(2));
        usleep(2000 * 1000); // 2000ms
    }

    private function then_the_pool_configs_match_the_auto_scale_config() {
        $res = $this->requiresPoolConfigs;
        $this->assertEquals('sales', $res->poolConfigs[0]->name());
        $this->assertEquals(['sales', 'sales_order'], $res->poolConfigs[0]->receiverIds());
        $this->assertEquals('default', $res->poolConfigs[1]->name());
        $this->assertEquals(['catalog'], $res->poolConfigs[1]->receiverIds());
    }

    private function then_the_receiver_to_pools_mapping_matches(array $mapping) {
        $this->assertEquals($mapping, $this->requiresPoolConfigs->receiverToPoolMapping);
    }

    private function then_the_message_info_file_matches_the_messages_sent() {
        $res = array_map('trim', file(__DIR__ . '/Fixtures/_message-info.txt'));
        sort($res);
        $this->assertEquals([
            'catalog: 1',
            'sales-order: 2',
            'sales-order: 2',
            'sales: 2',
            'sales: 2',
        ], $res);
    }
}
