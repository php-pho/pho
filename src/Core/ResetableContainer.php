<?php

namespace Pho\Core;

use DI\Container;
use DI\FactoryInterface;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;

class ResetableContainer extends Container
{
    public function reset()
    {
        $this->resolvedEntries = [
            self::class => $this,
            Container::class => $this,
            ContainerInterface::class => $this->delegateContainer,
            FactoryInterface::class => $this,
            InvokerInterface::class => $this,
        ];
    }
}
