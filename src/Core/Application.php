<?php

namespace Pho\Core;

use DI\Container;
use DI\ContainerBuilder;

class Application
{
    private $containerBuilder;
    private $container;

    public function __construct(ContainerBuilder $containerBuilder = null)
    {
        $this->containerBuilder = $containerBuilder;
    }

    public function register(ServiceProviderInterface $service_provider, array $opts = []): self
    {
        $service_provider->register($this->containerBuilder, $opts);

        return $this;
    }

    public function buildContainer(): Container
    {
        $this->container = $this->containerBuilder->build();

        return $this->container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function run()
    {
        $args = func_get_args();
        $program = array_shift($args);

        if (!method_exists($program, 'run')) {
            throw new \RuntimeException(sprintf("Program '%s' doesn't have 'run' method.", $program));
        }

        $container = $this->container ?: $this->buildContainer();

        return $container->call([$program, 'run'], $args);
    }
}
