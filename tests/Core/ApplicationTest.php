<?php

use DI\ContainerBuilder;
use Pho\Core\Application;
use Pho\Core\ServiceProviderInterface;
use Pho\TestCase;
use function DI\create;
use function DI\get;

class DumpProgram
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function run($prefix)
    {
        return $prefix.$this->name;
    }
}
class DumbServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'key' => 'value',
            'override' => 'old',
            'alias' => get('key'),
            DumbServiceProvider::class => $this,
            DumpProgram::class => create(DumpProgram::class)->constructor(get('key')),
        ];
        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}

class ApplicationTest extends TestCase
{
    public function testContainer()
    {
        $app = new Application(new ContainerBuilder());
        $dump_service_provider = new DumbServiceProvider();
        $app->register($dump_service_provider, ['override' => 'new']);
        $container1 = $app->buildContainer();
        $container2 = $app->getContainer();

        $this->assertSame($container1, $container2);
        $this->assertEquals('value', $container1->get('key'));
        $this->assertEquals('new', $container1->get('override'));
        $this->assertEquals('value', $container1->get('alias'));
        $this->assertSame($dump_service_provider, $container1->get(DumbServiceProvider::class));
    }

    public function testRun()
    {
        $app = new Application(new ContainerBuilder());
        $app->register(new DumbServiceProvider());
        $app->buildContainer();
        $result = $app->run(DumpProgram::class, 'This is a ');

        $this->assertEquals('This is a value', $result);
    }
}
