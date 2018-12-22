<?php

namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Pho\Http\ExceptionController;
use Pho\Http\MiddlewareSubscriber;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Pho\Http\Kernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use function DI\decorate;
use Pho\Routing\ControllerResolver;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = $opts;

        $def['http.request'] = function () {
            return Request::createFromGlobals();
        };

        $def[ExceptionListener::class] = autowire()
            ->constructor(
                get(ExceptionController::class),
                get(LoggerInterface::class),
                get('DEBUG'),
                get('CHARSET')
            );
        $def[ExceptionHandler::class] = autowire()
            ->constructor(
                get('DEBUG'),
                get('CHARSET')
            );
        $def[EventDispatcherInterface::class] = autowire(EventDispatcher::class)
            ->method('addSubscriber', get(ExceptionListener::class))
            ->method('addSubscriber', get(MiddlewareSubscriber::class));
        $def[ControllerResolverInterface::class] = autowire(ControllerResolver::class);
        //     ->constructor(get(LoggerInterface::class))
        //     ->method('setUrlMatcher', get(UrlMatcher::class))
        //     ->method('setContainer', get(ContainerInterface::class));
        $def[UrlGeneratorInterface::class] = get(Router::class);
        $def[RouterListener::class] = autowire()
            ->constructor(
                get(Router::class)
            );
        $def[Kernel::class] = decorate(function ($kernel, ContainerInterface $c) {
            $kernel->subscribe(RouterListener::class);

            return $kernel;
        });
        $def[RequestContext::class] = autowire()->method('fromRequest', get('http.request'));

        $containerBuilder->addDefinitions($def);
    }
}
