<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Logger;

/**
 * Bootloads core services.
 */
final class CoreBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ConfigurationBootloader::class,
    ];

    protected const SINGLETONS = [
        // core services and helpers
        FilesInterface::class => Files::class,
        MemoryInterface::class => [self::class, 'memory'],

        // debug and logging services
        Logger\ListenerRegistryInterface::class => Logger\ListenerRegistry::class,
        Logger\LogsInterface::class => Logger\LogFactory::class,
    ];

    private function memory(
        DirectoriesInterface $directories,
        FilesInterface $files
    ): MemoryInterface {
        return new Memory($directories->get('cache'), $files);
    }
}
