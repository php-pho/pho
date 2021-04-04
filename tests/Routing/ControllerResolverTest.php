<?php

use Monolog\Handler\NullHandler;
use Pho\Http\Controller;
use Pho\Routing\ControllerResolver;
use Pho\ServiceProvider\LogServiceProvider;
use Pho\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;

class DumbResolvedController extends Controller
{
    public function dummy()
    {
        return new Response('dummy');
    }
}

class ControllerResolverTest extends TestCase
{
    protected function registerServiceProviders($builder)
    {
        $log_service_provider = new LogServiceProvider();
        $log_service_provider->register($builder, [
            'logger.handler' => autowire(NullHandler::class),
        ]);
    }

    public function testGetController()
    {
        $dumb_controller = $this->container->get(DumbResolvedController::class);
        $resolver = $this->container->make(ControllerResolver::class);
        $request = Request::create('http://example.site/hello', 'GET');
        $request->attributes->set('_controller', [DumbResolvedController::class, 'dummy']);
        $controller = $resolver->getController($request);

        $object = $controller[0];
        $this->assertSame($dumb_controller, $object);
    }
}
