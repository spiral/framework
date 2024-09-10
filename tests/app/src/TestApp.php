<?php

declare(strict_types=1);

namespace Spiral\App;

use Spiral\Testing\TestableKernelInterface;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Bootloader\SnapshotsBootloader;
use Spiral\Bootloader\I18nBootloader;
use Spiral\Bootloader\Security\EncrypterBootloader;
use Spiral\Validation\Bootloader\ValidationBootloader;
use Spiral\Validator\Bootloader\ValidatorBootloader;
use Spiral\Bootloader\Security\FiltersBootloader;
use Spiral\Bootloader\Security\GuardBootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Bootloader\Http\ErrorHandlerBootloader;
use Spiral\Bootloader\Http\JsonPayloadsBootloader;
use Spiral\Bootloader\Http\CookiesBootloader;
use Spiral\Bootloader\Http\SessionBootloader;
use Spiral\Bootloader\Http\CsrfBootloader;
use Spiral\Bootloader\Http\PaginationBootloader;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Serializer\Bootloader\SerializerBootloader;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Distribution\Bootloader\DistributionBootloader;
use Spiral\Broadcasting\Bootloader\BroadcastingBootloader;
use Spiral\Broadcasting\Bootloader\WebsocketsBootloader;
use Spiral\Events\Bootloader\EventsBootloader;
use Spiral\Views\Bootloader\ViewsBootloader;
use Spiral\Bootloader\Views\TranslatedCacheBootloader;
use Spiral\Storage\Bootloader\StorageBootloader;
use Spiral\Bootloader\CommandBootloader;
use Spiral\Bootloader\DebugBootloader;
use Spiral\Bootloader\Debug\LogCollectorBootloader;
use Spiral\Bootloader\Debug\HttpCollectorBootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\App\Bootloader\AppBootloader;
use Spiral\App\Bootloader\AuthBootloader;
use Spiral\App\Bootloader\RoutesBootloader;
use Spiral\Bootloader;
use Spiral\Framework\Kernel;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Testing\Traits\TestableKernel;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

class TestApp extends Kernel implements TestableKernelInterface
{
    use TestableKernel;

    private array $disabledBootloaders = [];

    public const LOAD = [
        TokenizerListenerBootloader::class,

        // Telemetry
        TelemetryBootloader::class,

        // Core Services
        SnapshotsBootloader::class,
        I18nBootloader::class,

        // Validation, filtration, security
        EncrypterBootloader::class,
        ValidationBootloader::class,
        ValidatorBootloader::class,
        FiltersBootloader::class,
        GuardBootloader::class,

        // Dispatchers
        ConsoleBootloader::class,

        // HTTP extensions
        NyholmBootloader::class,
        RouterBootloader::class,
        ErrorHandlerBootloader::class,
        JsonPayloadsBootloader::class,
        CookiesBootloader::class,
        SessionBootloader::class,
        CsrfBootloader::class,
        PaginationBootloader::class,

        // Cache
        CacheBootloader::class,

        // Queue
        QueueBootloader::class,

        // Serializer
        SerializerBootloader::class,

        // SendIt
        MailerBootloader::class,

        // Scaffolder
        ScaffolderBootloader::class,

        // Distribution
        DistributionBootloader::class,

        // Broadcasting
        BroadcastingBootloader::class,
        WebsocketsBootloader::class,

        // Events
        EventsBootloader::class,

        // selects between session and cycle based on env configuration
        AuthBootloader::class,

        // Template engines and rendering
        StemplerBootloader::class,
        ViewsBootloader::class,
        TranslatedCacheBootloader::class,

        // Storage
        StorageBootloader::class,

        // Framework commands
        CommandBootloader::class,

        // Debug and debug extensions
        DebugBootloader::class,
        LogCollectorBootloader::class,
        HttpCollectorBootloader::class,

        PrototypeBootloader::class,
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
