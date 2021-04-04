<?php

namespace Pho\Http;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareSubscriber implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(ControllerEvent $event)
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

    public function onKernelResponse(ResponseEvent $event)
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
