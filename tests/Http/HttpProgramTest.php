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

class HttpProgramTest extends TestCase {
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

    public function testRun() {
        $program = $this->container->make(HttpProgram::class);
        
        ob_start();
        $program->run();
        $content = ob_get_flush();
        ob_clean();

        $this->assertEquals('world', $content);
    }
}