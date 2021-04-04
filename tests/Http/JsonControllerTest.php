<?php

use Pho\Http\JsonController;
use Pho\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DumJsonController extends JsonController
{
    public function proxyCall($method, $params)
    {
        return call_user_func_array([$this, $method], $params);
    }
}

class JsonControllerTest extends TestCase
{
    protected function containerDefinations()
    {
        return [];
    }

    protected function removeHeaderDateFromResponse($response)
    {
        if ($response instanceof Response) {
            $response->headers->remove('date');
        }
        return $response;
    }

    public function testJson()
    {
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

        $this->assertInstanceOf(ParameterBag::class, $this->getAttributeValue($controller, 'body'));

        $result1 = $controller->proxyCall('jsonValue', ['a', false, 'c']);
        $this->assertEquals('b', $result1);

        $result2 = $controller->proxyCall('jsonValue', ['b', false, 'c']);
        $this->assertEquals('c', $result2);

        $this->expectException(HttpException::class);
        $result2 = $controller->proxyCall('jsonValue', ['b', true, 'c']);
    }

    public function testSetRequestException()
    {
        try {
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
            throw new Exception('Error!');
        } catch (Exception $e) {
            $this->assertInstanceOf(HttpException::class, $e);
        }
    }
}
