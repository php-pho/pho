<?php

namespace Pho\Http\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class Session extends SymfonySession
{
    private $request;

    private $response;

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        if (method_exists($this->storage, 'setRequest')) {
            call_user_func([$this->storage, 'setRequest'], $request);
        }
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;

        if (method_exists($this->storage, 'setResponse')) {
            call_user_func([$this->storage, 'setResponse'], $response);
        }
    }

    public function start()
    {
        return $this->storage->start();
    }

    public function save()
    {
        return $this->storage->save();
    }
}
