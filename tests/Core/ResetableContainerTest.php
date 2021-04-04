<?php

use Pho\TestCase;

class ResetableContainerTest extends TestCase
{
    protected $container_class = 'Pho\Core\ResetableContainer';

    protected function containerDefinations()
    {
        return [
            'random' => function () {
                return rand(0, 999999999);
            },
        ];
    }

    public function testReset()
    {
        $old = $this->container->get('random');
        $same = $this->container->get('random');
        $this->container->reset();
        $after_reset = $this->container->get('random');

        $this->assertEquals($old, $same);
        $this->assertNotEquals($old, $after_reset, 'PINGO !! You are so lucky.');
    }
}
