<?php
namespace Pho\Http\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends SymfonySession {
    private $request;
    private $response;

    public function setRequest(Request $request) {
        $this->request = $request;

        if (method_exists($this->storage, 'setRequest')) {
            $this->storage->setRequest($request);
        }
    }

    public function setResponse(Response $response) {
        $this->response = $response;

        if (method_exists($this->storage, 'setResponse')) {
            $this->storage->setResponse($response);
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
