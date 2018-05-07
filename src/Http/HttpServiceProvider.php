<?php

namespace Pho\Http;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Pho\Routing\ControllerResolver;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = $opts;

        $def['http.request'] = function () {
            return Request::createFromGlobals();
        };

        $def[EventDispatcherInterface::class] = autowire(EventDispatcher::class);
        $def[ControllerResolverInterface::class] = autowire(ControllerResolver::class)
            ->method('setUrlMatcher', get(UrlMatcher::class))
            ->method('setContainer', get(ContainerInterface::class));

        $containerBuilder->addDefinitions($def);
    }
}
