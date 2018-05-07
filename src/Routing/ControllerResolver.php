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

    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
    }

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
        $route = $this->urlMatcher->matchRequest($request);
        $request->attributes->set('_controller', $route['_controller']);

        return parent::getController($request);
    }

    protected function instantiateController($class)
    {
        return $this->container->get($class);
    }
}
