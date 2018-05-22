<?php
namespace Pho\ServiceProvider;

use DI\ContainerBuilder;
use Pho\Core\ServiceProviderInterface;
use Pho\Http\Session\HmacCookieSessionStorage;
use Pho\Http\Session\Session;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use function DI\create;
use function DI\value;
use function DI\get;

class SessionServiceProvider implements ServiceProviderInterface {
    public function register(ContainerBuilder $containerBuilder, array $opts = [])
    {
        $def = array_merge([
            'session.hmac_secret' => '',
            HmacCookieSessionStorage::class => create()->constructor(get('session.hmac_secret')),
            SessionStorageInterface::class => get(HmacCookieSessionStorage::class),
            AttributeBagInterface::class => value(null),
            FlashBagInterface::class => value(null),
        ], $opts);

        $def[Session::class] = create()
            ->constructor(
                get(SessionStorageInterface::class),
                get(AttributeBagInterface::class),
                get(FlashBagInterface::class)
            );

        $containerBuilder->addDefinitions($def);
    }
}
