<?php

namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Console\ConsoleKernel;
use Pho\Core\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;
use function DI\create;
use function DI\get;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = [])
    {
        $def = [
            'console.name' => 'Pho Console',
            'console.version' => '1.0.0',
            'kernel.class' => null,
        ];

        $def[ConsoleKernel::class] = function ($c) {
            $kernelClass = $c->get('kernel.class');
            $console_kernel = $c->get($kernelClass);
            $console_kernel->commands();

            return $console_kernel;
        };

        $def[Application::class] = create()
            ->constructor(
                get('console.name'),
                get('console.version'),
                get(ContainerInterface::class)
            );

        $def['console'] = get(Application::class);

        $def = array_merge($def, $opts);

        $builder->addDefinitions($def);
    }
}
