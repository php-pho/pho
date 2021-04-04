<?php

namespace Pho\Http;

use Pho\Http\Session\Session;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionSubscriber implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $this->getSession();

        if (method_exists($session, 'setRequest')) {
            $session->setRequest($request);
        }

        if ($request->hasSession()) {
            return;
        }

        $request->setSession($session);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$session = $event->getRequest()->getSession()) {
            return;
        }

        if ($session->isStarted() || ($session instanceof Session && $session->isStarted())) {
            $response = $event->getResponse();

            if (method_exists($session, 'setResponse')) {
                call_user_func([$session, 'setResponse'], $response);
                $session->save();
            }

            $response
                ->setPrivate()
                ->setMaxAge(0)
                ->headers->addCacheControlDirective('must-revalidate');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 128],
            KernelEvents::RESPONSE => ['onKernelResponse', -1000],
        ];
    }

    protected function getSession()
    {
        return $this->container->get(Session::class);
    }
}
