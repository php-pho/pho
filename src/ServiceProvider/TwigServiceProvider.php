<?php

namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use function DI\autowire;
use function DI\get;

class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'twig.path' => null,
            'twig.options' => [],
        ];

        $def[LoaderInterface::class] = autowire(FilesystemLoader::class)
            ->method('addPath', get('twig.path'));
        $def[Environment::class] = autowire()
            ->constructor(get(LoaderInterface::class), get('twig.options'));
        $def['twig'] = get(Environment::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
