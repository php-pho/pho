<?php
namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use function DI\decorate;
use Pho\Core\ServiceProviderInterface;
use Pho\Http\Kernel;
use Pho\Http\Session\HmacCookieSessionStorage;
use Pho\Http\Session\Session;
use Pho\Http\SessionSubscriber;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use function DI\create;
use function DI\value;
use function DI\get;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = [
            'session.hmac_secret' => '',
            HmacCookieSessionStorage::class => create()->constructor(get('session.hmac_secret')),
            SessionStorageInterface::class => get(HmacCookieSessionStorage::class),
            AttributeBagInterface::class => value(null),
            FlashBagInterface::class => value(null),
        ];

        $def[Session::class] = create()
            ->constructor(
                get(SessionStorageInterface::class),
                get(AttributeBagInterface::class),
                get(FlashBagInterface::class)
            );
        $def[Kernel::class] = decorate(function ($kernel, ContainerInterface $c) {
            $kernel->subscribe(SessionSubscriber::class);

            return $kernel;
        });

        $def = array_merge($def, $opts);

        $containerBuilder->addDefinitions($def);
    }
}
