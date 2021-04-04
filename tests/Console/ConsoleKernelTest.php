<?php

use Pho\Console\ConsoleKernel;
use Pho\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function DI\autowire;

class ConsoleKernelConcrete extends ConsoleKernel
{
    public $out;

    public function commands()
    {
        $command = function (OutputInterface $output) {
            $output->write('Hello World');
        };

        $this->command('hello', $command);
    }
}

class ConsoleKernelTest extends TestCase
{
    protected function containerDefinations()
    {
        return [
            ConsoleKernel::class => autowire(ConsoleKernelConcrete::class)->method('commands'),
        ];
    }

    public function testRun()
    {
        $kernel = $this->container->make(ConsoleKernel::class);
        $this->assertClassHasAttribute('app', ConsoleKernel::class);
        $output = new BufferedOutput();
        $kernel->runCommand('hello', $output);

        $this->assertEquals('Hello World', $output->fetch());
    }
}
