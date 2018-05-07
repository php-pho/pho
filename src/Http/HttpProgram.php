<?php

namespace Pho\Http;

use Pho\Core\ProgramInterface;
use Psr\Container\ContainerInterface;

class HttpProgram implements ProgramInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run()
    {
        $request = $this->container->get('http.request');
        $response = $this->container->call([Kernel::class, 'handle'], [$request]);
        $response->send();
        $this->container->call([Kernel::class, 'terminate'], [$request, $response]);
    }
}
