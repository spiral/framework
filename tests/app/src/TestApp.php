<?php

declare(strict_types=1);

namespace Spiral\App;

use Spiral\App\Bootloader\AppBootloader;
use Spiral\App\Bootloader\AuthBootloader;
use Spiral\App\Bootloader\RoutesBootloader;
use Spiral\Bootloader;
use Spiral\Framework\Kernel;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Testing\Traits\TestableKernel;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

class TestApp extends Kernel implements \Spiral\Testing\TestableKernelInterface
{
    use TestableKernel;

    private array $disabledBootloaders = [];

    public const LOAD = [
        TokenizerListenerBootloader::class,

        // Telemetry
        \Spiral\Telemetry\Bootloader\TelemetryBootloader::class,

        // Core Services
        Bootloader\SnapshotsBootloader::class,
        Bootloader\I18nBootloader::class,

        // Validation, filtration, security
        Bootloader\Security\EncrypterBootloader::class,
        \Spiral\Validation\Bootloader\ValidationBootloader::class,
        \Spiral\Validator\Bootloader\ValidatorBootloader::class,
        Bootloader\Security\FiltersBootloader::class,
        Bootloader\Security\GuardBootloader::class,

        // Dispatchers
        \Spiral\Console\Bootloader\ConsoleBootloader::class,

        // HTTP extensions
        NyholmBootloader::class,
        Bootloader\Http\RouterBootloader::class,
        Bootloader\Http\ErrorHandlerBootloader::class,
        Bootloader\Http\JsonPayloadsBootloader::class,
        Bootloader\Http\CookiesBootloader::class,
        Bootloader\Http\SessionBootloader::class,
        Bootloader\Http\CsrfBootloader::class,
        Bootloader\Http\PaginationBootloader::class,

        // Cache
        \Spiral\Cache\Bootloader\CacheBootloader::class,

        // Queue
        \Spiral\Queue\Bootloader\QueueBootloader::class,

        // Serializer
        \Spiral\Serializer\Bootloader\SerializerBootloader::class,

        // SendIt
        \Spiral\SendIt\Bootloader\MailerBootloader::class,

        // Scaffolder
        \Spiral\Scaffolder\Bootloader\ScaffolderBootloader::class,

        // Distribution
        \Spiral\Distribution\Bootloader\DistributionBootloader::class,

        // Broadcasting
        \Spiral\Broadcasting\Bootloader\BroadcastingBootloader::class,
        \Spiral\Broadcasting\Bootloader\WebsocketsBootloader::class,

        // Events
        \Spiral\Events\Bootloader\EventsBootloader::class,

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
        Bootloader\DebugBootloader::class,
        Bootloader\Debug\LogCollectorBootloader::class,
        Bootloader\Debug\HttpCollectorBootloader::class,

        \Spiral\Prototype\Bootloader\PrototypeBootloader::class,
    ];

    public const APP = [
        AppBootloader::class,
        RoutesBootloader::class,
    ];

    protected function defineBootloaders(): array
    {
        $bootloaders = static::LOAD;

        // filter out disabled bootloaders
        return \array_filter($bootloaders, fn(string $bootloader): bool => !\in_array($bootloader, $this->disabledBootloaders, true));
    }

    /**
     * @param class-string<\Spiral\Boot\Bootloader\Bootloader> ...$bootloader
     */
    public function disableBootloader(string ...$bootloader): self
    {
        $this->disabledBootloaders = \array_merge($this->disabledBootloaders, $bootloader);

        return $this;
    }
}
