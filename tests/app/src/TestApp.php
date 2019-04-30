<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App;

use Spiral\App\Bootloader\AppBootloader;
use Spiral\Bootloader;
use Spiral\Console\Console;
use Spiral\Core\Kernel;

class TestApp extends Kernel
{
    const LOAD = [
        // Core Services
        Bootloader\DebugBootloader::class,
        Bootloader\SnapshotsBootloader::class,
        Bootloader\TranslationBootloader::class,

        // Validation, filtration, security
        Bootloader\Security\EncrypterBootloader::class,
        Bootloader\Security\ValidationBootloader::class,
        Bootloader\Security\FiltersBootloader::class,
        Bootloader\Security\RbacBootloader::class,

        // Dispatchers
        Bootloader\Http\HttpBootloader::class,
        Bootloader\ConsoleBootloader::class,

        // HTTP extensions
        Bootloader\Http\ErrorHandlerBootloader::class,
        Bootloader\Http\RouterBootloader::class,
        Bootloader\Http\SessionBootloader::class,
        Bootloader\Http\CookiesBootloader::class,
        Bootloader\Http\CsrfBootloader::class,

        // Data and Storage
        Bootloader\Database\DatabaseBootloader::class,
        Bootloader\Database\MigrationsBootloader::class,

        // Template engines and rendering
        //  Bootloader\Views\ViewsBootloader::class,
        //    Bootloader\Views\TranslateBootloader::class,

        // Extensions
        //  StemplerBootloader::class,

        // Framework commands
        Bootloader\CommandsBootloader::class
    ];

    const APP = [AppBootloader::class];

    /**
     * @param string $alias
     * @return mixed|null|object
     */
    public function get(string $alias)
    {
        return $this->container->get($alias);
    }

    /**
     * @return Console
     */
    public function console(): Console
    {
        return $this->get(Console::class);
    }
}