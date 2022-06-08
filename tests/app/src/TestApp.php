<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App;

use Psr\Container\ContainerInterface;
use Spiral\App\Bootloader\AppBootloader;
use Spiral\App\Bootloader\AuthBootloader;
use Spiral\Bootloader;
use Spiral\Bootloader\ExceptionHandlerBootloader;
use Spiral\Core\Container;
use Spiral\Framework\Kernel;
use Spiral\Stempler\Bootloader\StemplerBootloader;

class TestApp extends Kernel implements \Spiral\Testing\TestableKernelInterface
{
    public const LOAD = [
        // Core Services
        Bootloader\SnapshotsBootloader::class,
        Bootloader\I18nBootloader::class,

        // Validation, filtration, security
        Bootloader\Security\EncrypterBootloader::class,
        \Spiral\Validation\Bootloader\ValidationBootloader::class,
        Bootloader\Security\FiltersBootloader::class,
        Bootloader\Security\GuardBootloader::class,

        // Dispatchers
        \Spiral\Console\Bootloader\ConsoleBootloader::class,

        // HTTP extensions
        \Spiral\Http\Bootloader\DiactorosBootloader::class,
        Bootloader\Http\RouterBootloader::class,
        Bootloader\Http\ErrorHandlerBootloader::class,
        Bootloader\Http\JsonPayloadsBootloader::class,
        Bootloader\Http\CookiesBootloader::class,
        Bootloader\Http\SessionBootloader::class,
        Bootloader\Http\CsrfBootloader::class,
        Bootloader\Http\PaginationBootloader::class,

        // Cache
        \Spiral\Cache\Bootloader\CacheBootloader::class,

        // Broadcasting
        \Spiral\Broadcasting\Bootloader\BroadcastingBootloader::class,
        \Spiral\Broadcasting\Bootloader\WebsocketsBootloader::class,

        // Auth
        Bootloader\Auth\HttpAuthBootloader::class,

        // selects between session and cycle based on env configuration
        AuthBootloader::class,

        // Template engines and rendering
        StemplerBootloader::class,
        \Spiral\Views\Bootloader\ViewsBootloader::class,
        Bootloader\Views\TranslatedCacheBootloader::class,

        // Storage
        \Spiral\Storage\Bootloader\StorageBootloader::class,

        // Framework commands
        Bootloader\CommandBootloader::class,

        // Debug and debug extensions
        ExceptionHandlerBootloader::class,
        Bootloader\DebugBootloader::class,
        Bootloader\Debug\LogCollectorBootloader::class,
        Bootloader\Debug\HttpCollectorBootloader::class
    ];

    public const APP = [
        AppBootloader::class,
    ];

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRegisteredDispatchers(): array
    {
        return $this->dispatchers;
    }

    public function getRegisteredBootloaders(): array
    {
        return $this->bootloader->getClasses();
    }
}
