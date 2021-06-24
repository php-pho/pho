<?php

use Pho\Http\BeforeController;
use Pho\Http\DecoratedArgumentMetadataFactory;
use Pho\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DecoratedArgumentMetadataFactoryTestController
{
    public function hello($a, int $b = 1)
    {
        return $a . $b;
    }
}
class DecoratedArgumentMetadataFactoryTest extends TestCase
{
    public function testCreateArgumentMetadata()
    {
        $object = $this->container->make(DecoratedArgumentMetadataFactory::class);

        $controller = new DecoratedArgumentMetadataFactoryTestController();
        $result = $object->createArgumentMetadata([$controller, 'hello']);

        $this->assertEquals([
            new ArgumentMetadata('a', null, false, false, null),
            new ArgumentMetadata('b', 'int', false, true, 1)
        ], $result);

        $controller1 = new BeforeController(null, [$controller, 'hello']);
        $result1 = $object->createArgumentMetadata($controller1);

        $this->assertEquals([
            new ArgumentMetadata('a', null, false, false, null),
            new ArgumentMetadata('b', 'int', false, true, 1)
        ], $result1);
    }
}
