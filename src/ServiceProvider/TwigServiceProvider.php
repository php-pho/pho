<?php
namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use Twig_Environment;
use Twig_LoaderInterface;
use Twig_Loader_Filesystem;
use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'twig.path' => null,
            'twig.options' => [],
        ];

        $def[Twig_LoaderInterface::class] = autowire(Twig_Loader_Filesystem::class)
            ->method('addPath', get('twig.path'));
        $def[Twig_Environment::class] = autowire()
            ->constructor(get(Twig_LoaderInterface::class), get('twig.options'));
        $def['twig'] = get(Twig_Environment::class);

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
