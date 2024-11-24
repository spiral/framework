<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Core\Container;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\LoaderRegistryInterface;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Tests\Router\Stub\TestLoader;

final class TestRouterBootloader extends Bootloader
{
    public function defineDependencies(): array
    {
        return [
            RouterBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [
            LoaderRegistryInterface::class => fn(Container $container) => new LoaderRegistry([
                new PhpFileLoader($container, $container),
                new TestLoader(),
            ]),
        ];
    }
}
