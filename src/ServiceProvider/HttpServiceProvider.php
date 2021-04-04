<?php

namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Pho\Http\DecoratedArgumentMetadataFactory;
use Pho\Http\ExceptionController;
use Pho\Http\Kernel;
use Pho\Http\MiddlewareSubscriber;
use Pho\Routing\ControllerResolver;
use Pho\Routing\RouteLoader;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Twig\Environment;
use function DI\autowire;
use function DI\decorate;
use function DI\get;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'kernel.class' => null,
            'router.resource' => null,
            'router.options' => [],
        ];

        $def['http.request'] = function () {
            return Request::createFromGlobals();
        };
        $def[ErrorListener::class] = autowire()
            ->constructor(
                get(ExceptionController::class),
                get(LoggerInterface::class),
                get('DEBUG')
            );
        $def[ErrorHandler::class] = autowire()
            ->constructor(
                null,
                get('DEBUG')
            );
        $def[EventDispatcherInterface::class] = autowire(EventDispatcher::class)
            ->method('addSubscriber', get(ErrorListener::class))
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
        $def[HttpKernel::class] = autowire()
            ->constructor(
                get(EventDispatcherInterface::class),
                get(ControllerResolverInterface::class),
                get(RequestStack::class),
                get(ArgumentResolverInterface::class)
            );
        $def[ArgumentResolverInterface::class] = autowire(ArgumentResolver::class)
            ->constructorParameter('argumentMetadataFactory', get(ArgumentMetadataFactoryInterface::class));
        $def[ArgumentMetadataFactoryInterface::class] = function (ContainerInterface $c) {
            return new DecoratedArgumentMetadataFactory(new ArgumentMetadataFactory());
        };
        $def[RequestContext::class] = autowire()->method('fromRequest', get('http.request'));
        $def[Environment::class] = decorate(function (Environment $twig, ContainerInterface $c) {
            $twig->addExtension($c->get(RoutingExtension::class));
            return $twig;
        });

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
