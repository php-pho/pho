<?php

namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use Twig_Environment;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;

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
        $def[Twig_Environment::class] = get(Environment::class);
        $def['twig'] = get(Environment::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
