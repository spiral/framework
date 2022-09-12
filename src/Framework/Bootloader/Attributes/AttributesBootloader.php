<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

class AttributesBootloader extends Bootloader
{
    public function init(BinderInterface $binder): void
    {
        $binder->bindSingleton(
            ReaderInterface::class,
            static function (ContainerInterface $container): ReaderInterface {
                $factory = new Factory();

                if ($container->has(CacheInterface::class)) {
                    $cache = $container->get(CacheInterface::class);
                    \assert($cache instanceof CacheInterface);

                    $factory = $factory->withCache($cache);
                } elseif ($container->has(CacheItemPoolInterface::class)) {
                    $cachePool = $container->get(CacheItemPoolInterface::class);
                    \assert($cachePool instanceof CacheItemPoolInterface);

                    $factory = $factory->withCache($cachePool);
                }

                return $factory->create();
            }
        );
    }
}
