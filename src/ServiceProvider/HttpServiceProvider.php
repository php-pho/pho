<?php

namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use function DI\decorate;
use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Pho\Http\ExceptionController;
use Pho\Http\MiddlewareSubscriber;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Pho\Http\Kernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Pho\Routing\ControllerResolver;
use Pho\Routing\RouteLoader;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = array_merge([
            'kernel.class' => null,
            'router.resource' => null,
            'router.options' => [],
        ], $opts);

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
        $def[Router::class] = autowire()
            ->constructor(
                get(RouteLoader::class),
                get('router.resource'),
                get('router.options')
            );
        $def[UrlGeneratorInterface::class] = get(Router::class);
        $def[RouterListener::class] = autowire()
            ->constructor(
                get(Router::class)
            );
        $def[Kernel::class] = function (ContainerInterface $c) {
            $kernelClass = $c->get('kernel.class');
            $kernel = $c->get($kernelClass);

            $kernel->subscribe(RouterListener::class);
            $kernel->stacks();
            $kernel->events();

            return $kernel;
        };
        $def[RequestContext::class] = autowire()->method('fromRequest', get('http.request'));

        $containerBuilder->addDefinitions($def);
    }
}
