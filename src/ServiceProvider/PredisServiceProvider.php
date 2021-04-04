<?php

namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Predis\Client;
use function DI\autowire;
use function DI\get;

class PredisServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'predis.connection' => 'tcp://127.0.0.1:6379',
        ];

        $def[Client::class] = autowire()
            ->constructor(get('predis.connection'));
        $def['predis'] = get(Client::class);
        $def['redis'] = get(Client::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
