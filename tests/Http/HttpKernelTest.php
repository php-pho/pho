<?php

use Pho\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Pho\Http\Kernel;
use Pho\Http\HttpProgram;
use function DI\get;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pho\ServiceProvider\HttpServiceProvider;
use Pho\Routing\RouteLoader;
use Pho\Routing\Routing;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use Pho\ServiceProvider\LogServiceProvider;
use Psr\Log\LoggerInterface;
use Monolog\Handler\NullHandler;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pho\Http\MiddlewareSubscriber;
use Pho\Http\SessionSubscriber;
use Pho\ServiceProvider\SessionServiceProvider;

class HttpProgramTestKernel extends Kernel {
    public function stacks() {}
    public function events() {}
}

class HttpProgramTestRouter extends RouteLoader {
    public function routes(Routing $routing) {
        $routing->get('/hello', function() {
            return new Response('world');
        }, 'hello', [
            '_before' => function (Request $request) {
                $request->attributes->set('before_controller', true);
            }
        ]);
    }
}

class DumbHttpKernel implements HttpKernelInterface {
    public function __construct() {}

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true) {
        return new Response('dumb_kernel');
    }
}

class DumbHttpSubscriber implements EventSubscriberInterface {
    public function onTerminate(PostResponseEvent $event) {
        $request = $event->getRequest();
        $request->attributes->set('terminated_by_subscriber', true);
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::TERMINATE => 'onTerminate',
        ];
    }
}

class HttpKernelTest extends TestCase {
    protected function registerServiceProviders($builder) {
        $log_service_provider = new LogServiceProvider();
        $log_service_provider->register($builder, [
            'logger.handler' => autowire(NullHandler::class),
        ]);

        $http_service_provider = new HttpServiceProvider();
        $http_service_provider->register($builder, [
            'kernel.class' => HttpProgramTestKernel::class,
            'http.request' => function() {
                return Request::create('http://example.site/hello', 'GET');
            },
            RouteLoader::class => autowire(HttpProgramTestRouter::class),
        ]);

        $session_service_provider = new SessionServiceProvider();
        $session_service_provider->register($builder, [
            'session.hmac_secret' => 'secret',
        ]);
    }

    public function testHttpKernel() {
        $kernel = $this->container->make(Kernel::class);
        $push = $kernel->push(DumbHttpKernel::class);

        $this->assertSame($kernel, $push);

        $stackBuilder = Assert::getObjectAttribute($kernel, 'stackBuilder');
        $stacks = Assert::getObjectAttribute($stackBuilder, 'specs');

        $this->assertEquals(1, $stacks->count());

        $kernel->on(KernelEvents::TERMINATE, function(PostResponseEvent $event) {
            $request = $event->getRequest();
            $request->attributes->set('terminated', true);
        });
        $kernel->subscribe(DumbHttpSubscriber::class);
        
        $request = Request::create('http://example.site/path', 'GET');
        $response = $kernel->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('dumb_kernel', $response->getContent());

        $kernel->terminate($request, $response);
        $this->assertEquals(true, $request->attributes->get('terminated', false));
        $this->assertEquals(true, $request->attributes->get('terminated_by_subscriber', false));
    }

    public function testMiddlewareSubscriber() {
        $kernel = $this->container->make(Kernel::class);
        $kernel->subscribe(MiddlewareSubscriber::class);

        $request = $this->container->get('http.request');
        $response = $kernel->handle($request);

        $this->assertEquals(true, $request->attributes->get('before_controller', false));
    }

    public function testSessionSubscriber() {
        $kernel = $this->container->make(Kernel::class);

        $request = Request::create('http://example.site/path', 'GET', [], [
            'PHO_SESSION' => 'YToxOntzOjE1OiJfc2YyX2F0dHJpYnV0ZXMiO2E6MTp7czo1OiJoZWxsbyI7czo1OiJ3b3JsZCI7fX0=|f75aac88e70269ce86bff907bac3468a5f393249610c95fea752992969bad8b1',
        ]);
        $response = $kernel->handle($request);
        $session = $request->getSession();

        $this->assertEquals('world', $session->get('hello'));
    }

    public function testHttpProgram() {
        $program = $this->container->make(HttpProgram::class);
        
        ob_start();
        $program->run();
        $content = ob_get_flush();
        ob_clean();

        $this->assertEquals('world', $content);
    }
}