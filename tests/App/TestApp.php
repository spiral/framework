<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App;

use Spiral\Bootloader;
use Spiral\Console\ConsoleCore;
use Spiral\Core\Kernel;
use Spiral\Stempler\Bootloader\StemplerBootloader;

class TestApp extends Kernel
{
    const LOAD = [
        // Core Services
        Bootloader\System\DebugBootloader::class,
        Bootloader\System\SnapshotsBootloader::class,
        Bootloader\System\TranslatorBootloader::class,

        // Validation, filtration, security
        Bootloader\Security\EncrypterBootloader::class,
        Bootloader\Security\ValidationBootloader::class,
        Bootloader\Security\FiltersBootloader::class,
        Bootloader\Security\RBACBootloader::class,

        // Dispatchers
        Bootloader\Dispatcher\HttpBootloader::class,
        Bootloader\Dispatcher\RoadRunnerBootloader::class,
        Bootloader\Dispatcher\JobsBootloader::class,
        Bootloader\Dispatcher\ConsoleBootloader::class,

        // HTTP extensions
        Bootloader\Http\ErrorPageBootloader::class,
        Bootloader\Http\RouterBootloader::class,
        Bootloader\Http\SessionBootloader::class,
        Bootloader\Http\CookiesBootloader::class,
        Bootloader\Http\CsrfBootloader::class,

        // Data and Storage
        Bootloader\Database\DatabaseBootloader::class,
        Bootloader\Database\MigrationsBootloader::class,

        // Template engines and rendering
        Bootloader\Views\ViewsBootloader::class,
        Bootloader\Views\TranslateBootloader::class,

        // Extensions
        StemplerBootloader::class,
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
     * @return ConsoleCore
     */
    public function console(): ConsoleCore
    {
        return $this->get(ConsoleCore::class);
    }
}