<?php

namespace Pho\Console;

use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;

abstract class ConsoleKernel
{
    protected $container;
    protected $app;

    public function __construct(ContainerInterface $container, Application $app)
    {
        $this->container = $container;
        $this->app = $app;
    }

    protected function command(string $expression, $callable, array $aliases = [])
    {
        $this->app->command($expression, $callable, $aliases);
    }

    public function run(): int
    {
        return $this->app->run();
    }

    abstract public function commands();
}
