<?php

declare(strict_types=1);

namespace Spiral\Attributes\Bootloader;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;

class AttributesBootloader extends Bootloader
{
    public function init(Container $container): void
    {
        $container->bindSingleton(ReaderInterface::class, function () use ($container) {
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

            return $factory->create();
        });
    }
}
