<?php
namespace Pho\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareSubscriber implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if ($request->attributes->has('_before')) {
            $before = $request->attributes->get('_before');
            $before = is_array($before) ? $before : [$before];

            $wrapController = null;

            for ($i = count($before) - 1; $i >= 0; $i--) {
                $middleware = $before[$i];
                $callable =  is_string($middleware) ? $this->container->get($middleware) : $middleware;

                if (!is_callable($callable)) {
                    throw new \Exception('Before middleware is not callable !');
                }

                $wrapController = new BeforeController($callable, $wrapController ?: $controller);
                $wrapController->setRequest($request);
            }

            $event->setController($wrapController);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->has('_after')) {
            $after = $request->attributes->get('_after');
            $after = is_array($after) ? $after : [$after];

            foreach ($after as $middleware) {
                $callable =  is_string($middleware) ? $this->container->get($middleware) : $middleware;

                $afterResponse = call_user_func_array($callable, [$request, $response]);
                if ($afterResponse && $afterResponse instanceof Response) {
                    return $event->setResponse($afterResponse);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 512],
            KernelEvents::RESPONSE => ['onKernelResponse', 512],
        ];
    }
}
