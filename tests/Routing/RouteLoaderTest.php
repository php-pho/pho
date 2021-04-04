<?php

use Pho\Routing\RouteLoader;
use Pho\Routing\Routing;
use Pho\TestCase;

class DummyRouteLoader extends RouteLoader
{
    public function routes(Routing $routing)
    {
        $routing->get('/', ['a', 'b'], 'index');
    }
}

class RouteLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new DummyRouteLoader();
        $this->assertEquals(true, $loader->supports('test'));

        $collection = $loader->load('test');

        $this->assertEquals('/', $collection->get('index')->getPath());
    }
}
