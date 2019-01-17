<?php

use Pho\TestCase;
use Pho\Core\ContainerBuilderFactory;
use PHPUnit\Framework\Assert;

class ContainerBuilderFactoryTest extends TestCase {
    protected function containerDefinations() {
        return [];
    }

    public function dataEnvironments() {
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
    public function testEnvironments($env, $args, $autowiring, $annotations, $compiled_dir, $proxies_dir, $container_class) {
        $builder = call_user_func_array([ContainerBuilderFactory::class, $env], $args);

        $builder_autowiring = Assert::getObjectAttribute($builder, 'useAutowiring');
        $builder_annotations = Assert::getObjectAttribute($builder, 'useAnnotations');
        $builder_compiled_dir = Assert::getObjectAttribute($builder, 'compileToDirectory');
        $builder_proxies_dir = Assert::getObjectAttribute($builder, 'proxyDirectory');
        $builder_container_class = Assert::getObjectAttribute($builder, 'containerClass');

        $this->assertEquals($autowiring, $builder_autowiring);
        $this->assertEquals($annotations, $builder_annotations);
        $this->assertEquals($compiled_dir, $builder_compiled_dir);
        $this->assertEquals($proxies_dir, $builder_proxies_dir);
        $this->assertEquals($container_class, $builder_container_class);
    }
}