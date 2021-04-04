<?php

use Pho\Http\Session\HmacCookieSessionStorage;
use Pho\Http\Session\Session;
use Pho\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTest extends TestCase
{
    public function testSetGetRequestResponse()
    {
        $storage = new HmacCookieSessionStorage();
        $session = new Session($storage);
        $request = Request::create('http://example.site/path', 'GET');
        $response = new Response('content here');

        $session->setRequest($request);
        $session->setResponse($response);

        $this->assertSame($request, $session->getRequest());
        $this->assertSame($response, $this->getAttributeValue($session, 'response'));
        $this->assertSame($request, $this->getAttributeValue($storage, 'request'));
        $this->assertSame($response, $this->getAttributeValue($storage, 'response'));
    }

    public function testStart()
    {
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
