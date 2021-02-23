<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\ConfigurationBootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Core\Container;

class ConfigBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        CoreBootloader::class,
    ];

    public function boot(ConfigurationBootloader $configuration, Container $container): void
    {
        $configuration->addLoader('yaml', $container->get(TestLoader::class));
    }
}
