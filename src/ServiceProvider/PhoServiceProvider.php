<?php
namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;

class PhoServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'DEBUG' => false,
            'CHARSET' => 'utf-8',
        ];

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
