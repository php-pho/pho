<?php
namespace Pho\Http;

use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionController
{
    protected $handler;
    protected $request;

    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->handler = $exceptionHandler;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke()
    {
        $exception = $this->request->attributes->get('exception');

        return new Response($this->handler->getHtml($exception), $exception->getStatusCode(), $exception->getHeaders());
    }
}
