<?php

use Pho\TestCase;
use Pho\Http\ExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionControllerTest extends TestCase {
    public function testInvoke() {
        $exception = new NotFoundHttpException('uhoh');
        $controller = $this->container->make(ExceptionController::class);
        $request = Request::create('http://example.site/path', 'GET');
        $request->attributes->set('exception', $exception);
        $controller->setRequest($request);

        $result = $controller();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertContains('NotFoundHttpException', $result->getContent());
    }
}