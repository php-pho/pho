<?php

namespace Pho\Routing;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as SymfonyControllerResolver;
use Psr\Log\LoggerInterface;

class ControllerResolver extends SymfonyControllerResolver
{
    private $container;

    public function __construct(LoggerInterface $logger = null, ContainerInterface $container)
    {
        parent::__construct($logger);
        $this->container = $container;
    }

    public function getController(Request $request)
    {
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
