<?php

namespace Pho\Core;

use DI\ContainerBuilder;

final class ContainerBuilderFactory
{
    public static function development(): ContainerBuilder
    {
        return static::create(true, false, null, null);
    }

    public static function testing(): ContainerBuilder
    {
        return static::create(true, false, null, null, 'Pho\Core\ResetableContainer');
    }

    public static function lightweight(): ContainerBuilder
    {
        return static::create(false, false, null, null);
    }

    public static function production(
        $compiled_dir = null,
        $proxies_dir = null
    ): ContainerBuilder {
        return static::create(true, false, $compiled_dir, $proxies_dir);
    }

    public static function create(
        $autowiring = true,
        $annotations = false,
        $compiled_dir = null,
        $proxies_dir = null,
        $container_class = 'DI\Container'
    ): ContainerBuilder {
        $builder = new ContainerBuilder($container_class);
        $builder->useAutowiring($autowiring);
        $builder->useAnnotations($annotations);

        if ($compiled_dir) {
            $builder->enableCompilation($compiled_dir);
        }

        if ($proxies_dir) {
            $builder->writeProxiesToFile(true, $proxies_dir);
        }

        return $builder;
    }
}
