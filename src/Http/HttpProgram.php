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
        $kernel = $this->container->get(Kernel::class);
        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }
}
