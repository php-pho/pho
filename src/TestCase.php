<?php
namespace Pho;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use DI\ContainerBuilder;

abstract class TestCase extends PHPUnitTestCase {
    protected $container_class = 'DI\Container';
    protected $container;

    public function setUp() {
        $builder = new ContainerBuilder($this->container_class);
        $builder->useAutowiring(true);
        $this->registerServiceProviders($builder);
        $builder->addDefinitions($this->containerDefinations());
        $this->container = $builder->build();
    }

    protected function containerDefinations() {
        return [];
    }

    protected function registerServiceProviders($builder) {
        // Nothing here
    }
}