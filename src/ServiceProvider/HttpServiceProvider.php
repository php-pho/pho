<?php

namespace Pho\ServiceProvider;

use function DI\autowire;
use function DI\get;
use function DI\decorate;
use Twig_Environment;
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
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Pho\Http\Kernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Pho\Routing\ControllerResolver;
use Pho\Routing\RouteLoader;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\RequestStack;
use Pho\Http\DecoratedArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use function DI\create;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;

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
        $def[HttpKernel::class] = autowire()
            ->constructor(
                get(EventDispatcherInterface::class),
                get(ControllerResolverInterface::class),
                get(RequestStack::class),
                get(ArgumentResolverInterface::class)
            );
        $def[ArgumentResolverInterface::class] = autowire(ArgumentResolver::class)
            ->constructorParameter('argumentMetadataFactory', get(ArgumentMetadataFactoryInterface::class));
        $def[ArgumentMetadataFactoryInterface::class] = function(ContainerInterface $c) {
            return new DecoratedArgumentMetadataFactory(new ArgumentMetadataFactory()); 
        };            
        $def[RequestContext::class] = autowire()->method('fromRequest', get('http.request'));
        $def[Twig_Environment::class] = decorate(function(Twig_Environment $twig, ContainerInterface $c) {
            $twig->addExtension($c->get(RoutingExtension::class));
            return $twig;
        });

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
