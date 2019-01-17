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
        $builder->addDefinitions($this->containerDefinations());
        $this->container = $builder->build();
    }

    abstract protected function containerDefinations();
}