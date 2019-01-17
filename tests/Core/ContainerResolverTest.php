<?php

use Pho\TestCase;
use Pho\Core\ContainerResolver;

class ContainerResolverTest extends TestCase {
    protected function containerDefinations() {
        return [
            'key' => 'value',
        ];
    }

    public function testInvoke() {
        $resolver = $this->container->make(ContainerResolver::class);
        $resolved = $resolver('key');

        $this->assertEquals('value', $resolved);
    }
}