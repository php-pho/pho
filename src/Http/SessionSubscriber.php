<?php
namespace Pho\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pho\Http\Session\Session;

class SessionSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $this->getSession();

        if (method_exists($session, 'setRequest')) {
            $session->setRequest($request);
        }

        if (null === $this->session || $request->hasSession()) {
            return;
        }

        $request->setSession($this->session);
    }

    public function onKernelResponse(FilterResponseEvent $event)
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
                $session->setResponse($response);
                $this->session->save();
            }

            $response
                ->setPrivate()
                ->setMaxAge(0)
                ->headers->addCacheControlDirective('must-revalidate');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
            KernelEvents::RESPONSE => array('onKernelResponse', -1000),
        );
    }

    protected function getSession()
    {
        return $this->session;
    }
}
