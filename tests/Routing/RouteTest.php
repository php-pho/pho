<?php

use PHPUnit\Framework\TestCase;
use Pho\Routing\Route;

class RouteTest extends TestCase {
    public function testConstructor() {
        $route = new Route('');

        $this->assertEquals('', $route->getPath());
    }

    public function testSetPath() {
        $route = new Route('/');

        $this->assertEquals('/', $route->getPath());

        $r = $route->setPath('');
        $this->assertSame($route, $r);
        $this->assertEquals('', $route->getPath());
    }
}