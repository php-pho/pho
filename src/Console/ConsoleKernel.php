<?php

namespace Pho\Console;

use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConsoleKernel
{
    protected $container;

    protected $app;

    public function __construct(ContainerInterface $container, Application $app)
    {
        $this->container = $container;
        $this->app = $app;
    }

    protected function command(string $expression, $callable, array $aliases = [], string $description = '')
    {
        $command = $this->app->command($expression, $callable, $aliases);
        $command->setDescription($description);
        return $command;
    }

    public function run(): int
    {
        return $this->app->run();
    }

    public function runCommand($command, OutputInterface $output = null)
    {
        return $this->app->runCommand($command, $output);
    }

    abstract public function commands();
}
