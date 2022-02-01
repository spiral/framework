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

    public function boot(Container $container, AbstractKernel $kernel): void
    {
        $kernel->starting(static function (AbstractKernel $kernel) use ($container) {
            $container->bind('hij', 'foo');

            $kernel->starting(static function () use ($container) {
                $container->bind('ijk', 'foo');
            });
        });

        $kernel->started(function (AbstractKernel $kernel) use ($container) {
            $container->bind('jkl', 'foo');

            $kernel->starting(function () use ($container) {
                $container->bind('klm', 'foo');
            });

            $kernel->started(function () use ($container) {
                $container->bind('lmn', 'foo');
            });
        });

        $container->bind('efg', 'foo');
    }

    public function start(ConfigurationBootloader $configuration, AbstractKernel $kernel, Container $container): void
    {
        // won't be executed
        $kernel->starting(function (AbstractKernel $kernel) use ($container) {
            $container->bind('ghi', 'foo');
        });

        $kernel->started(function (AbstractKernel $kernel) use ($container) {
            $container->bind('mno', 'foo');
        });

        $configuration->addLoader('yaml', $container->get(TestLoader::class));
    }
}
