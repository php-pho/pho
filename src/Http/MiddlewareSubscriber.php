<?php
namespace Pho\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareSubscriber implements EventSubscriberInterface
{
    private $controllerResolver;

    public function __construct(ControllerResolverInterface $controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if ($request->attributes->has('_before')) {
            $before = $request->attributes->get('_before');
            $wrapController = new BeforeController($before, $controller);
            $wrapController->setRequest($request);
            $event->setController($wrapController);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->has('_after')) {
            $after = $request->attributes->get('_after');
            $afterResponse = call_user_func_array($after, [$request, $response]);
            if ($afterResponse) {
                $event->setResponse($afterResponse);
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
