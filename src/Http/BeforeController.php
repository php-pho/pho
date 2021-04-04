<?php

namespace Pho\Http;

use Symfony\Component\HttpFoundation\Request;

class BeforeController
{
    private $before;

    private $controller;

    private $request;

    public function __construct($before, $controller)
    {
        $this->before = $before;
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke()
    {
        if ($middlewareResponse = call_user_func($this->before, $this->request)) {
            return $middlewareResponse;
        }

        return call_user_func_array($this->controller, func_get_args());
    }
}
