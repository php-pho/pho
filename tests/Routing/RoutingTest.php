<?php

use Pho\Routing\Route;
use Pho\Routing\Routing;
use Pho\TestCase;
use Symfony\Component\Routing\RouteCollection;

class RoutingTest extends TestCase
{
    /**
     * @dataProvider dataProviderMap
     */
    public function testMap($method, $path, $handler, $name, $defaults = [], $requirements = [], $options = [])
    {
        $collection = new RouteCollection();
        $routing = new Routing($collection);

        $routing->map($method, $path, $handler, $name, $defaults, $requirements, $options);

        $this->assertSame($collection, $routing->getRouteCollection());

        $route = $collection->get($name);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($handler, $route->getDefault('_controller'));
        $this->assertArraySubset($defaults, $route->getDefaults());
        $this->assertEquals($requirements, $route->getRequirements());
        $this->assertArraySubset($options, $route->getOptions());
        $this->assertEquals(explode('|', $method), $route->getMethods());
    }

    /**
     * @dataProvider dataProviderCommonMethods
     */
    public function testCommomMethods($method, $path, $handler, $name, $defaults = [], $requirements = [], $options = [])
    {
        $collection = new RouteCollection();
        $routing = new Routing($collection);

        $routing->$method($path, $handler, $name, $defaults, $requirements, $options);

        $this->assertSame($collection, $routing->getRouteCollection());

        $route = $collection->get($name);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($handler, $route->getDefault('_controller'));
        $this->assertArraySubset($defaults, $route->getDefaults());
        $this->assertEquals($requirements, $route->getRequirements());
        $this->assertArraySubset($options, $route->getOptions());
        $this->assertEquals(1, count($route->getMethods()));
        $this->assertContains(strtoupper($method), $route->getMethods());
    }

    public function dataProviderMap()
    {
        return [
            ['GET', '', 'dumb_handler1', 'index'],
            ['POST', '', 'dumb_handler2', 'index_post', ['a' => 'b']],
            ['PUT', '/api/order/123', 'dumb_handler3', 'api_put', [], ['x' => 'y']],
            ['HEAD', '/news.html', 'dumb_handler4', 'news'],
            ['DELETE', '/order/123', 'dumb_handler5', 'delete_order', [], [], ['o' => 'm']],
            ['GET|POST', '/test', 'dumb_handler6', 'get_post_test'],
        ];
    }

    public function dataProviderCommonMethods()
    {
        return [
            ['get', '', 'dumb_handler1', 'index'],
            ['post', '', 'dumb_handler2', 'index_post', ['a' => 'b']],
            ['put', '/api/order/123', 'dumb_handler3', 'api_put', [], ['x' => 'y']],
            ['head', '/news.html', 'dumb_handler4', 'news'],
            ['delete', '/order/123', 'dumb_handler5', 'delete_order', [], [], ['o' => 'm']],
            ['patch', '/order/123', 'dumb_handler6', 'patchh_order', [], [], ['o' => 'm']],
            ['options', '/order/123', 'dumb_handler7', 'options_order', [], [], ['o' => 'm']],
        ];
    }
}
