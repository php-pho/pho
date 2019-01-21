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

class HttpProgramTestKernel extends Kernel {
    public function stacks() {}
    public function events() {}
}

class HttpProgramTestRouter extends RouteLoader {
    public function routes(Routing $routing) {
        $routing->get('/hello', function() {
            return new Response('world');
        }, 'hello');
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

    public function testHttpProgram() {
        $program = $this->container->make(HttpProgram::class);
        
        ob_start();
        $program->run();
        $content = ob_get_flush();
        ob_clean();

        $this->assertEquals('world', $content);
    }
}