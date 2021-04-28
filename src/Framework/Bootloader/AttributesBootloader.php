<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;

class AttributesBootloader extends Bootloader
{
    /**
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        $container->bindSingleton(ReaderInterface::class, function () use ($container) {
            $factory = new Factory();

            if ($container->has(CacheInterface::class)) {
                $factory = $factory->withCache(
                    $container->get(CacheInterface::class)
                );
            } elseif ($container->has(CacheItemInterface::class)) {
                $factory = $factory->withCache(
                    $container->get(CacheItemInterface::class)
                );
            }

            return $factory->create();
        });
    }
}
