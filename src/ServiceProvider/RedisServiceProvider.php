<?php
namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use function DI\get;
use Pho\Core\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Redis;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = array_merge([
            'redis.host' => '127.0.0.1',
            'redis.port' => 6379,
            'redis.timeout' => 1,
            'redis.persistent' => false,
            'redis.database' => 0,
        ], $opts);

        $def[Redis::class] = function (ContainerInterface $container) {
            // Redis class reflection will be wrong so we define it manually way
            $redis = new Redis();
            $connect_method = $container->get('redis.persistent') ? 'pconnect' : 'connect';
            call_user_func_array([$redis, $connect_method], [
                $container->get('redis.host'),
                intval($container->get('redis.port')),
                intval($container->get('redis.timeout')),
            ]);
            $redis->select(intval($container->get('redis.database')));
            return $redis;
        };
        $def['redis'] = get(Redis::class);

        $containerBuilder->addDefinitions($def);
    }
}
