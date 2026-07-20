<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\ConfigurationBootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Core\Container;

class ConfigBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        CoreBootloader::class,
    ];

    public function init(Container $container, AbstractKernel $kernel): void
    {
        $kernel->booting(static function (AbstractKernel $kernel) use ($container): void {
            $container->bind('hij', 'foo');

            $kernel->booting(static function () use ($container): void {
                $container->bind('ijk', 'foo');
            });
        });

        $kernel->booted(static function (AbstractKernel $kernel) use ($container): void {
            $container->bind('jkl', 'foo');

            $kernel->booting(static function () use ($container): void {
                $container->bind('klm', 'foo');
            });

            $kernel->booted(static function () use ($container): void {
                $container->bind('lmn', 'foo');
            });
        });

        $container->bind('efg', 'foo');
    }

    public function boot(ConfigurationBootloader $configuration, AbstractKernel $kernel, Container $container): void
    {
        // won't be executed
        $kernel->booting(static function (AbstractKernel $kernel) use ($container): void {
            $container->bind('ghi', 'foo');
        });

        $kernel->booted(static function (AbstractKernel $kernel) use ($container): void {
            $container->bind('mno', 'foo');
        });

        $configuration->addLoader('yaml', $container->get(TestLoader::class));
    }
}
