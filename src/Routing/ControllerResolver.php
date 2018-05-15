<?php

namespace Pho\Routing;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as SymfonyControllerResolver;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class ControllerResolver extends SymfonyControllerResolver
{
    private $urlMatcher;
    private $container;

    public function setUrlMatcher(UrlMatcherInterface $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getController(Request $request)
    {
        $matchedRoute = $this->urlMatcher->matchRequest($request);
        $request->attributes->add($matchedRoute);

        $controller = parent::getController($request);

        if (is_object($controller)) {
            if (method_exists($controller, 'setRequest')) {
                $controller->setRequest($request);
            }
        }

        return $controller;
    }

    public function instantiateController($class)
    {
        return $this->container->get($class);
    }
}
