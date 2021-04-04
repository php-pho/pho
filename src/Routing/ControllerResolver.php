<?php

namespace Pho\Routing;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as SymfonyControllerResolver;

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
        return $this->container->get($class);
    }
}
