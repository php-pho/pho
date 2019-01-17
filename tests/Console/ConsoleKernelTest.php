<?php

use Pho\TestCase;
use Pho\Console\ConsoleKernel;
use DI\Container;
use DI\ContainerBuilder;
use Symfony\Component\Console\Output\OutputInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Output\BufferedOutput;
use function DI\autowire;

class ConsoleKernelConcrete extends ConsoleKernel {
    public $out;

    public function commands()
    {
        $command = function (OutputInterface $output) {
            $output->write('Hello World');
        };

        $this->command('hello', $command);
    }
}

class ConsoleKernelTest extends TestCase {
    protected function containerDefinations() {
        return [
            ConsoleKernel::class => autowire(ConsoleKernelConcrete::class)->method('commands'),
        ];
    }

    public function testRun() {
        $kernel = $this->container->make(ConsoleKernel::class);
        $console_app = Assert::getObjectAttribute($kernel, 'app');
        $output = new BufferedOutput();
        $console_app->runCommand('hello', $output);

        $this->assertEquals('Hello World', $output->fetch());
    }
}