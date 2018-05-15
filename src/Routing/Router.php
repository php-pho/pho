<?php

namespace Pho\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    private $collection;

    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    public function group($prefix, callable $groupCallback, array $defaults = []): self
    {
        $childRouteCollection = new RouteCollection();
        $childRouter = new static($childRouteCollection);
        call_user_func_array($groupCallback, [$childRouter]);
        $childRouteCollection->addPrefix($prefix);
        $childRouteCollection->addDefaults($defaults);
        $this->collection->addCollection($childRouteCollection);

        return $childRouter;
    }

    public function map(string $method, string $path, $handler, string $name, array $defaults = []): self
    {
        $path = sprintf('/%s', ltrim($path, '/'));
        $defaults['_controller'] = $handler;
        $route = (new Route($path, $defaults))->setMethods(explode('|', $method));
        $this->collection->add($name, $route);

        return $this;
    }

    public function get(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('GET', $path, $handler, $name, $defaults);
    }

    public function post(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('POST', $path, $handler, $name, $defaults);
    }

    public function put(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('PUT', $path, $handler, $name, $defaults);
    }

    public function patch(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('PATCH', $path, $handler, $name, $defaults);
    }

    public function delete(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('DELETE', $path, $handler, $name, $defaults);
    }

    public function head(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('HEAD', $path, $handler, $name, $defaults);
    }

    public function options(string $path, $handler, string $name, array $defaults = []): self
    {
        return $this->map('OPTIONS', $path, $handler, $name, $defaults);
    }

    public function routes()
    {
        // Nothing here
    }
}
