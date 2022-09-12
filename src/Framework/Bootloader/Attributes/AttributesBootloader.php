<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\FactoryInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

class AttributesBootloader extends Bootloader
{
    public function init(BinderInterface $binder): void
    {
        $binder->bindSingleton(ReaderInterface::class, function (ContainerInterface $container) {
            $factory = new Factory();

            if ($container->has(CacheInterface::class)) {
                $factory = $factory->withCache(
                    $container->get(CacheInterface::class)
                );
            } elseif ($container->has(CacheItemPoolInterface::class)) {
                $factory = $factory->withCache(
                    $container->get(CacheItemPoolInterface::class)
                );
            }

            \assert($factory instanceof FactoryInterface);

            return $factory->create();
        });
    }
}
