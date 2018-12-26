<?php

namespace Pho\Http;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;

class DecoratedArgumentMetadataFactory implements ArgumentMetadataFactoryInterface
{
    private $factory;

    public function __construct(ArgumentMetadataFactory $factory) {
        $this->factory = $factory;
    }

    public function createArgumentMetadata($controller)
    {
        while ($controller instanceof BeforeController) {
            $controller = $controller->getController();
        }

        return $this->factory->createArgumentMetadata($controller);
    }
}
