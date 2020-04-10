<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\Message\{HandleCatalogMessage, HandleSalesMessage, HandleSalesOrderMessage};
use Krak\SymfonyMessengerAutoScale\Tests\Feature\Fixtures\RequiresSupervisorPoolConfigs;

return static function(ContainerConfigurator $configurator) {
    $configurator
    ->services()
        ->defaults()
            ->public()
        ->set(RequiresSupervisorPoolConfigs::class)
            ->args([ref('krak.messenger_auto_scale.supervisor_pool_configs')])
        ->set(HandleCatalogMessage::class)
            ->tag('messenger.message_handler')
        ->set(HandleSalesMessage::class)
            ->tag('messenger.message_handler')
        ->set(HandleSalesOrderMessage::class)
            ->tag('messenger.message_handler')
    ;
};
