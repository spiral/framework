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
        $kernel->booting(static function (AbstractKernel $kernel) use ($container) {
            $container->bind('hij', 'foo');

            $kernel->booting(static function () use ($container) {
                $container->bind('ijk', 'foo');
            });
        });

        $kernel->booted(function (AbstractKernel $kernel) use ($container) {
            $container->bind('jkl', 'foo');

            $kernel->booting(function () use ($container) {
                $container->bind('klm', 'foo');
            });

            $kernel->booted(function () use ($container) {
                $container->bind('lmn', 'foo');
            });
        });

        $container->bind('efg', 'foo');
    }

    public function boot(ConfigurationBootloader $configuration, AbstractKernel $kernel, Container $container): void
    {
        // won't be executed
        $kernel->booting(function (AbstractKernel $kernel) use ($container) {
            $container->bind('ghi', 'foo');
        });

        $kernel->booted(function (AbstractKernel $kernel) use ($container) {
            $container->bind('mno', 'foo');
        });

        $configuration->addLoader('yaml', $container->get(TestLoader::class));
    }
}
