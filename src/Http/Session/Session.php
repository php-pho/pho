<?php
namespace Pho\Http\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends SymfonySession {
    public function start(Request $request = null)
    {
        return $this->storage->start($request);
    }

    public function save(Response $response = null)
    {
        $this->storage->save($response);
    }
}
