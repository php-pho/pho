<?php

use Pho\TestCase;
use Pho\Http\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DI\get;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pho\Http\Session\HmacCookieSessionStorage;
use Pho\Http\Session\Session;
use Pho\Http\JsonController;
use Symfony\Component\HttpFoundation\ParameterBag;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DumJsonController extends JsonController {
    public function proxyCall($method, $params) {
        return call_user_func_array([$this, $method], $params);
    }
}

class JsonControllerTest extends TestCase {
    protected function containerDefinations() {
        return [];
    }

    protected function removeHeaderDateFromResponse($response) {
        if ($response instanceof Response) {
            $response->headers->remove('date');
        }
        return $response;
    }

    public function testJson() {
        $controller = new DumJsonController($this->container);
        $request = Request::create(
            'http://example.site/path',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"a":"b"}'
        );
        $controller->setRequest($request);

        $this->assertAttributeInstanceOf(ParameterBag::class, 'body', $controller);
        
        $result1 = $controller->proxyCall('jsonValue', ['a', false, 'c']);
        $this->assertEquals('b', $result1);

        $result2 = $controller->proxyCall('jsonValue', ['b', false, 'c']);
        $this->assertEquals('c', $result2);

        $this->expectException(HttpException::class);
        $result2 = $controller->proxyCall('jsonValue', ['b', true, 'c']);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testSetRequestException() {
        $controller = new DumJsonController($this->container);
        $request = Request::create(
            'http://example.site/path',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"a:"b"}'
        );
        $controller->setRequest($request);
    }
}