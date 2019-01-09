<?php

namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseManager;
use Pho\Core\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Illuminate\Events\Dispatcher;

class EloquentServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'db.connection' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => null,
                'username' => 'root',
                'password' => null,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => null,
            ],
        ];

        $def[Manager::class] = autowire()
            ->method('addConnection', get('db.connection'))
            ->method('setEventDispatcher', get(Dispatcher::class))
            ->method('setAsGlobal')
            ->method('bootEloquent');
        $def[DatabaseManager::class] = function (ContainerInterface $c) {
            return $c->get(Manager::class)->getDatabaseManager();
        };
        $def['capsule'] = get(Manager::class);
        $def['db'] = get(DatabaseManager::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
