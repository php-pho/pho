<?php

use PHPUnit\Framework\TestCase;
use Pho\Http\Session\HmacCookieSessionStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pho\Http\Session\Session;

class SessionTest extends TestCase {
    public function testSetGetRequestResponse() {
        $storage = new HmacCookieSessionStorage();
        $session = new Session($storage);
        $request = Request::create('http://example.site/path', 'GET');
        $response = new Response('content here');

        $session->setRequest($request);
        $session->setResponse($response);

        $this->assertSame($request, $session->getRequest());
        $this->assertAttributeSame($response, 'response', $session);

        $this->assertAttributeSame($request, 'request', $storage);
        $this->assertAttributeSame($response, 'response', $storage);
    }

    public function testStart() {
        $storage = new HmacCookieSessionStorage();
        $session = new Session($storage);
        $request = Request::create('http://example.site/path', 'GET');
        $response = new Response('content here');

        $session->setRequest($request);
        $session->setResponse($response);

        $session->start();
        $this->assertEquals(true, $storage->isStarted());
    }
}