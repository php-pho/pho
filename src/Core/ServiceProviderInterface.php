<?php

namespace Pho\Core;

use DI\ContainerBuilder;

interface ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = []);
}
