<?php

namespace Pho\Http;

use Pho\Routing\Router;
use Stack\Builder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Kernel
{
    private $stackBuilder;
    private $httpKernel;
    private $router;
    private $resolvedKernel;

    public function __construct(Builder $builder, HttpKernel $httpKernel, Router $router)
    {
        $this->stackBuilder = $builder;
        $this->httpKernel = $httpKernel;
        $this->router = $router;
    }

    public function push(HttpKernelInterface $kernel)
    {
        $this->stackBuilder->push($kernel);

        return $this;
    }

    public function handle(Request $request): Response
    {
        if (!$this->resolvedKernel) {
            $this->resolvedKernel = $this->stackBuilder->resolve($this->httpKernel);
        }

        return $this->resolvedKernel->handle($request);
    }

    public function terminate($request, $response)
    {
        return $this->httpKernel->terminate($request, $response);
    }
}
