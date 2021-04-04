<?php

namespace Pho\Routing;

use Symfony\Component\Routing\RouteCollection;

interface RoutingAbstract
{
    public function __construct(RouteCollection $collection = null);

    public function getRouteCollection(): RouteCollection;
}
