<?php

namespace Pho\Core;

use DI\ContainerBuilder;

final class ContainerBuilderFactory
{
    public static function development(): ContainerBuilder
    {
        return static::create(true, false, null, null);
    }

    public static function lightweight(): ContainerBuilder
    {
        return static::create(false, false, null, null);
    }

    public static function production(
        $autowiring = true,
        $annotations = false,
        $compiled_dir = null,
        $proxies_dir = null
    ): ContainerBuilder {
        return static::create($autowiring, $annotations, $compiled_dir, $proxies_dir);
    }

    public static function create(
        $autowiring = true,
        $annotations = false,
        $compiled_dir = null,
        $proxies_dir = null
    ): ContainerBuilder {
        $builder = new ContainerBuilder();
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
