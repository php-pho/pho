<?php

namespace Pho\Http;

use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionController
{
    protected $handler;

    protected $request;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->handler = $errorHandler;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke()
    {
        $exception = $this->request->attributes->get('exception');
        $handler = $this->handler;
        $handler->setExceptionHandler([$handler, 'renderException']);
        ob_start();
        $handler->handleException($exception);
        $response = ob_get_clean();

        if ($exception instanceof HttpExceptionInterface) {
            return new Response($response, $exception->getStatusCode(), $exception->getHeaders());
        }

        return new Response($response, 500);
    }
}
