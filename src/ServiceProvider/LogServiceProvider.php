<?php
namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pho\Core\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class LogServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'logger.name' => 'Pho',
            'logger.stream' => false,
            'logger.level' => Logger::DEBUG,
        ];

        $def['logger.handler'] = autowire(StreamHandler::class)
            ->constructor(get('logger.stream'), get('logger.level'));
        $def[LoggerInterface::class] = autowire(Logger::class)
            ->constructor(get('logger.name'))
            ->method('pushHandler', get('logger.handler'));
        $def['logger'] = get(LoggerInterface::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
