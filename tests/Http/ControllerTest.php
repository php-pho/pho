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

class DumbController extends Controller {
    public function proxyCall($method, $params) {
        return call_user_func_array([$this, $method], $params);
    }
}

class DumpUrlGenerator implements UrlGeneratorInterface {
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH) {
        return '/'.$name;
    }
    public function setContext(RequestContext $context){
        // Nothing
    }
    public function getContext() {
        // Do nothing
    }
}

class DumpTwig {
    public function render($template, $data) {
        return $template.'=>'.json_encode($data);
    }
}

class ControllerTest extends TestCase {
    protected function containerDefinations() {
        return [
            'key' => 'value',
            'twig' => get(DumpTwig::class),
            UrlGeneratorInterface::class => get(DumpUrlGenerator::class),
        ];
    }

    public function testFlash() {
        $controller = new DumbController($this->container);
        $session = new Session(new HmacCookieSessionStorage());
        $request = Request::create('http://example.site/path', 'GET');
        $request->setSession($session);
        $session->setRequest($request);
        $controller->setRequest($request);
        
        $result = $controller->proxyCall('redirectWithFlash', ['hello', [], 'danger', 'BOOM']);

        $this->assertEquals([
            [
                'type' => 'danger',
                'content' => 'BOOM'
            ]
        ], $session->getFlashBag()->get('message'));
        $this->assertEquals(new RedirectResponse('/hello'), $result);
    }

    protected function removeHeaderDateFromResponse($response) {
        if ($response instanceof Response) {
            $response->headers->remove('date');
        }
        return $response;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testController($method, $params, $expected, $request = null) {
        $controller = new DumbController($this->container);
        $request = $request ?: Request::create(
            'http://example.site/path?q=keyword',
            'POST',
            ['input1' => 'value1']
        );
        $controller->setRequest($request);
        $result = $controller->proxyCall($method, $params);

        $this->assertEquals(
            $this->removeHeaderDateFromResponse($expected),
            $this->removeHeaderDateFromResponse($result)
        );
    }

    public function dataProvider() {
        return [
            [
                'get', ['key'], 'value'
            ],
            [
                'json', [['hello' => 'world'], 200], new JsonResponse(['hello' => 'world'], 200)
            ],
            [
                'json', [['hello' => 'world'], 200], new JsonResponse(['hello' => 'world'], 200)
            ],
            [
                'json', [['hello' => 'world'], 404, ['X-Header' => 'X-Men']], new JsonResponse(['hello' => 'world'], 404, ['X-Header' => 'X-Men'])
            ],
            [
                'json', [['hello' => 'world'], 200, [], false], new JsonResponse(['hello' => 'world'], 200, [], false)
            ],
            [
                'json', ['{"a":"b"}', 200, [], true], new JsonResponse('{"a":"b"}', 200, [], true)
            ],
            [
                'text', ['okay', 200], new Response('okay', 200)
            ],
            [
                'text', ['okay', 404, ['X-Header' => 'X-Men']], new Response('okay', 404, ['X-Header' => 'X-Men'])
            ],
            [
                'redirect', ['/test', 302], new RedirectResponse('/test', 302)
            ],
            [
                'redirect', ['/test', 301, ['X-Header' => 'X-Men']], new RedirectResponse('/test', 301, ['X-Header' => 'X-Men'])
            ],
            [
                'redirectFor', ['hello', [], 302], new RedirectResponse('/hello', 302)
            ],
            [
                'redirectFor', ['ok', [], 301, ['X-Header' => 'X-Men']], new RedirectResponse('/ok', 301, ['X-Header' => 'X-Men'])
            ],
            [
                'render', ['home', ['a' => 'b'], 200, ['X-Header' => 'X-Men']], new Response('home=>{"a":"b"}', 200, ['X-Header' => 'X-Men'])
            ],
            [
                'getPostData', [], ['input1' => 'value1']
            ],
            [
                'getPostData', ['input1'], 'value1'
            ],
            [
                'getPostData', ['notexists', 'default'], 'default'
            ],
            [
                'getQueryParam', [], ['q' => 'keyword']
            ],
            [
                'getQueryParam', ['q'], 'keyword'
            ],
            [
                'getQueryParam', ['notexists', 'default'], 'default'
            ],
        ];
    }
}