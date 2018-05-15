<?php
namespace Pho\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionSubsciber implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $this->getSession($request);
        if (null === $session || $request->hasSession()) {
            return;
        }

        $request->setSession($session);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$session = $event->getRequest()->getSession()) {
            return;
        }

        if ($session->isStarted() || ($session instanceof Session && $session->hasBeenStarted())) {
            $event->getResponse()
                ->setPrivate()
                ->setMaxAge(0)
                ->headers->addCacheControlDirective('must-revalidate');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
            // low priority to come after regular response listeners, same as SaveSessionListener
            KernelEvents::RESPONSE => array('onKernelResponse', -1000),
        );
    }

    protected function getSession(Request $request) {

    }
}
