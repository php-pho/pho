<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pho\Http\BeforeController;

class BeforeControllerTest extends TestCase {
    /**
     * @dataProvider dataProvider
     */
    public function testBeforeController($expected, $before) {
        $request = Request::create('http://example.site/path', 'GET');
        $controller = function (Request $req) {
            return new Response('hello');
        };
        $beforeController = new BeforeController($before, $controller);
        $beforeController->setRequest($request);
        
        $this->assertSame($controller, $beforeController->getController());

        $response = $beforeController($request);

        $this->assertEquals('world', $request->attributes->get('hello'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function dataProvider() {
        return [
            [
                'hello', function (Request $req) {
                    $req->attributes->set('hello', 'world');
                }
            ],
            [
                'HELLO', function (Request $req) {
                    $req->attributes->set('hello', 'world');
                    return new Response('HELLO');
                }
            ],
        ];
    }
}