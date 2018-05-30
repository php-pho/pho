<?php
namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;

class PhoServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = array_merge([
            'DEBUG' => false,
            'CHARSET' => 'utf-8',
        ], $opts);

        $containerBuilder->addDefinitions($def);
    }
}
