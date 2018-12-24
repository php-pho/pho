<?php

namespace Pho\Http;

use Psr\Container\ContainerInterface;
use Stack\Builder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class Kernel
{
    private $container;
    private $stackBuilder;
    private $httpKernel;
    private $dispatcher;
    private $resolvedKernel;

    public function __construct(ContainerInterface $container, Builder $builder, HttpKernel $httpKernel, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->stackBuilder = $builder;
        $this->httpKernel = $httpKernel;
        $this->dispatcher = $dispatcher;
    }

    public function push($kernel)
    {
        $kernel = is_string($kernel) ? $this->container->get($kernel) : $kernel;

        if (!($kernel instanceof HttpKernelInterface)) {
            throw new \InvalidArgumentException("Kernel must be HttpKernelInterface object !");
        }

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

    public function on($eventName, $callback, $priority = 0) : self
    {
        $this->dispatcher->addListener($eventName, $callback, $priority);

        return $this;
    }

    public function subscribe($subscriber) : self
    {
        $subscriber = is_string($subscriber) ? $this->container->get($subscriber) : $subscriber;

        if (!($subscriber instanceof EventSubscriberInterface)) {
            throw new \InvalidArgumentException("Subscriber must be EventSubscriberInterface object !");
        }

        $this->dispatcher->addSubscriber($subscriber);

        return $this;
    }

    abstract public function stacks();
    abstract public function events();
}
