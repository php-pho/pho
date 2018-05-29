<?php

namespace Pho\Routing;

use Psr\Container\ContainerInterface;
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
        if (!$request->attributes->has('_controller')) {
            $matchedRoute = $this->urlMatcher->matchRequest($request);
            $request->attributes->add($matchedRoute);
        }

        $controller = parent::getController($request);

        if (is_object($controller) && method_exists($controller, 'setRequest')) {
            $controller->setRequest($request);
        } elseif (is_array($controller) && method_exists($controller[0], 'setRequest')) {
            $controller[0]->setRequest($request);
        }

        return $controller;
    }

    public function instantiateController($class)
    {
        $controller = $this->container->get($class);

        return $controller;
    }
}
