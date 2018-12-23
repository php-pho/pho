<?php
namespace Pho\ServiceProvider;

use Pho\Core\ServiceProviderInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;
use function DI\object;
use function DI\get;
use function DI\create;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = [])
    {
        $def = array_merge([
            'console.name' => 'Pho Console',
            'console.version' => '1.0.0',
            'console.register_func' => null,
        ], $opts);

        $def[Application::class] = create()
            ->constructor(
                get('console.name'),
                get('console.version'),
                get(ContainerInterface::class)
            );
        $def['console'] = get(Application::class);

        $builder->addDefinitions($def);
    }
}
