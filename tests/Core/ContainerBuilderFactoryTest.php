<?php

use Pho\Core\ContainerBuilderFactory;
use Pho\TestCase;

class ContainerBuilderFactoryTest extends TestCase
{
    public function dataEnvironments()
    {
        return [
            ['development', [], true, false, null, null, 'DI\Container'],
            ['testing', [], true, false, null, null, 'Pho\Core\ResetableContainer'],
            ['lightweight', [], false, false, null, null, 'DI\Container'],
            ['production', ['compiled', 'proxies'], true, false, 'compiled', 'proxies', 'CompiledContainer'],
        ];
    }

    /**
     * @dataProvider dataEnvironments
     */
    public function testEnvironments($env, $args, $autowiring, $annotations, $compiled_dir, $proxies_dir, $container_class)
    {
        $builder = call_user_func_array([ContainerBuilderFactory::class, $env], $args);

        $this->assertEquals($autowiring, $this->getAttributeValue($builder, 'useAutowiring'));
        $this->assertEquals($annotations, $this->getAttributeValue($builder, 'useAnnotations'));
        $this->assertEquals($compiled_dir, $this->getAttributeValue($builder, 'compileToDirectory'));
        $this->assertEquals($proxies_dir, $this->getAttributeValue($builder, 'proxyDirectory'));
        $this->assertEquals($container_class, $this->getAttributeValue($builder, 'containerClass'));
    }
}
