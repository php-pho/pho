<?php

namespace Pho;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected $container_class = 'DI\Container';

    protected $container;

    protected function setUp(): void
    {
        $builder = new ContainerBuilder($this->container_class);
        $builder->useAutowiring(true);
        $this->registerServiceProviders($builder);
        $builder->addDefinitions($this->containerDefinations());
        $this->container = $builder->build();
    }

    protected function containerDefinations()
    {
        return [];
    }

    protected function registerServiceProviders($builder)
    {
        // Nothing here
    }

    protected function assertArraySubset($expectedSubset, $actualArray)
    {
        foreach ($expectedSubset as $key => $value) {
            $this->assertArrayHasKey($key, $actualArray);
            $this->assertSame($value, $actualArray[$key]);
        }
    }

    protected function getAttributeValue($object, $attributeName)
    {
        $reflectionProperty = new \ReflectionProperty(get_class($object), $attributeName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }
}
