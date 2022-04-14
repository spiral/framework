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
use Spiral\App\Bootloader\WSBootloader;
use Spiral\Boot\BootloadManager;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader;
use Spiral\Console\Console;
use Spiral\Core\Container;
use Spiral\Framework\Kernel;
use Spiral\Stempler\Bootloader\StemplerBootloader;

class TestApp extends Kernel
{
    public const LOAD = [
        // Core Services
        Bootloader\SnapshotsBootloader::class,
        Bootloader\I18nBootloader::class,

        // Validation, filtration, security
        Bootloader\Security\EncrypterBootloader::class,
        Bootloader\Security\ValidationBootloader::class,
        Bootloader\Security\FiltersBootloader::class,
        Bootloader\Security\GuardBootloader::class,

        // Dispatchers
        Bootloader\Jobs\JobsBootloader::class,
        Bootloader\GRPC\GRPCBootloader::class,
        \Spiral\Console\Bootloader\ConsoleBootloader::class,

        // HTTP extensions
        Bootloader\Http\DiactorosBootloader::class,
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

        // Websocket authentication
        Bootloader\Http\WebsocketsBootloader::class,

        // selects between session and cycle based on env configuration
        AuthBootloader::class,

        // Data and Storage
        Bootloader\Database\DatabaseBootloader::class,
        Bootloader\Database\MigrationsBootloader::class,

        Bootloader\Cycle\CycleBootloader::class,
        Bootloader\Cycle\AnnotatedBootloader::class,
        Bootloader\Cycle\ProxiesBootloader::class,

        // Template engines and rendering
        StemplerBootloader::class,
        Bootloader\Views\ViewsBootloader::class,
        Bootloader\Views\TranslatedCacheBootloader::class,

        // Storage
        Bootloader\Storage\StorageBootloader::class,

        // Framework commands
        Bootloader\CommandBootloader::class,

        // Debug and debug extensions
        Bootloader\DebugBootloader::class,
        Bootloader\Debug\LogCollectorBootloader::class,
        Bootloader\Debug\HttpCollectorBootloader::class
    ];

    public const APP = [
        AppBootloader::class,
        WSBootloader::class
    ];

    /**
     * @param string $alias
     * @return mixed|null|object
     */
    public function get(string $alias)
    {
        return $this->container->get($alias);
    }

    /**
     * @param string $alias
     * @return string
     */
    public function dir(string $alias): string
    {
        return $this->container->get(DirectoriesInterface::class)->get($alias);
    }

    /**
     * @return Console
     */
    public function console(): Console
    {
        return $this->get(Console::class);
    }

    /**
     * @return Container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return EnvironmentInterface
     */
    public function getEnvironment(): EnvironmentInterface
    {
        return $this->container->get(EnvironmentInterface::class);
    }

    public function getBootloadManager(): BootloadManager
    {
        return $this->bootloader;
    }
}
