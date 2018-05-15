<?php

namespace Pho\Console;

use Pho\Core\ProgramInterface;
use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;

class ConsoleProgram implements ProgramInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run()
    {
        $this->container->call([ConsoleKernel::class, 'run']);
    }
}
