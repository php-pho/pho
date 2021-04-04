<?php

namespace Pho\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

abstract class RouteLoader extends Loader
{
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();
        $routing = new Routing($routes);
        $this->routes($routing);

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return true;
    }

    abstract public function routes(Routing $routing);
}
