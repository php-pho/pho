<?php

use PHPUnit\Framework\TestCase;
use Pho\Http\Session\HmacCookieSessionStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionBagProxy;

class HmacCookieSessionStorageTest extends TestCase {
    public function testSaveStorage() {
        $storage = new HmacCookieSessionStorage('secret');
        $request = Request::create('http://example.site/path', 'GET');
        $response = new Response('content here');
        $storage->setRequest($request);
        $storage->setResponse($response);

        // test set, get name
        $storage->setName('PHO_SESS');
        $this->assertEquals('PHO_SESS', $storage->getName());

        // bag
        $data = [];
        $attributeBag = new AttributeBag();
        $bag = new SessionBagProxy($attributeBag, $data, $usageIndex);
        $storage->registerBag($bag);
        $storage->setMetadataBag();

        // load session
        $storage->start();
        $this->assertSame($bag, $storage->getBag($bag->getName()));
        $this->assertEquals(true, $storage->isStarted());

        // set session value
        $bag->getBag()->set('hello', 'world');
        
        // save
        $storage->save();
        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('PHO_SESS', $cookies[0]->getName());
        $this->assertEquals(
            'YToxOntzOjE1OiJfc2YyX2F0dHJpYnV0ZXMiO2E6MTp7czo1OiJoZWxsbyI7czo1OiJ3b3JsZCI7fX0=|f75aac88e70269ce86bff907bac3468a5f393249610c95fea752992969bad8b1',
            $cookies[0]->getValue()
        );
    }

    public function testLoad() {
        $storage = new HmacCookieSessionStorage('secret');
        $request = Request::create('http://example.site/path', 'GET', [], [
            'PHO_SESS' => 'YToxOntzOjE1OiJfc2YyX2F0dHJpYnV0ZXMiO2E6MTp7czo1OiJoZWxsbyI7czo1OiJ3b3JsZCI7fX0=|f75aac88e70269ce86bff907bac3468a5f393249610c95fea752992969bad8b1',
        ]);
        $response = new Response('content here');
        $data = [];
        $attributeBag = new AttributeBag();
        $bag = new SessionBagProxy($attributeBag, $data, $usageIndex);
        $storage->setRequest($request);
        $storage->setResponse($response);
        $storage->setName('PHO_SESS');
        $storage->registerBag($bag);
        $storage->setMetadataBag();
        $storage->start();
        
        $this->assertEquals(true, $storage->isStarted());
        $this->assertEquals('world', $bag->getBag()->get('hello'));
    }
}