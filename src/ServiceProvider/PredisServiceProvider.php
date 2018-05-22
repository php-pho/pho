<?php
namespace Pho\ServiceProvider;

use function DI\autowire;
use DI\ContainerBuilder;
use function DI\get;
use Pho\Core\ServiceProviderInterface;
use Predis\Client;

class PredisServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = array_merge([
            'predis.connection' => 'tcp://127.0.0.1:6379',
        ], $opts);

        $def[Client::class] = autowire()
            ->constructor(get('predis.connection'));
        $def['predis'] = get(Client::class);
        $def['redis'] = get(Client::class);

        $containerBuilder->addDefinitions($def);
    }
}
