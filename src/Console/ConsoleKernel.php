<?php
namespace Pho\Console;

use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;

abstract class ConsoleKernel {
    private $container;
    private $app;

    public function __construct(ContainerInterface $container, Application $app)
    {
        $this->container = $container;
        $this->app = $app;
    }

    protected function command($expression, $callable, array $aliases = []) {
        $this->app->command($expression, $callable, $aliases);
    }

    public function run() {
        $this->app->run();
    }

    abstract public function commands();
}
