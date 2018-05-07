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

    public function group($prefix, callable $groupCallback): self
    {
        $childRouteCollection = new RouteCollection();
        $childRouter = new static($childRouteCollection);
        call_user_func_array($groupCallback, [$childRouter]);
        $childRouteCollection->addPrefix($prefix);
        $this->collection->addCollection($childRouteCollection);

        return $childRouter;
    }

    public function map(string $method, string $path, $handler, string $name): self
    {
        $path = sprintf('/%s', ltrim($path, '/'));
        $route = (new Route($path, [
            '_controller' => $handler,
        ]))->setMethods(explode('|', $method));
        $this->collection->add($name, $route);

        return $this;
    }

    public function get(string $path, $handler, string $name): self
    {
        return $this->map('GET', $path, $handler, $name);
    }

    public function post(string $path, $handler, string $name): self
    {
        return $this->map('POST', $path, $handler, $name);
    }

    public function put(string $path, $handler, string $name): self
    {
        return $this->map('PUT', $path, $handler, $name);
    }

    public function patch(string $path, $handler, string $name): self
    {
        return $this->map('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, $handler, string $name): self
    {
        return $this->map('DELETE', $path, $handler, $name);
    }

    public function head(string $path, $handler, string $name): self
    {
        return $this->map('HEAD', $path, $handler, $name);
    }

    public function options(string $path, $handler, string $name): self
    {
        return $this->map('OPTIONS', $path, $handler, $name);
    }

    public function routes()
    {
        // Nothing here
    }
}
