<?php

declare(strict_types=1);

namespace Spiral\Framework;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Exception\BootException;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

abstract class Kernel extends AbstractKernel
{
    /**
     * Framework specific bootloaders
     *
     * @deprecated Use {@see defineSystemBootloaders()} method instead. Will be removed in v4.0
     */
    protected const SYSTEM = [
        CoreBootloader::class,
        TokenizerBootloader::class,
    ];

    /**
     * Application specific bootloaders
     *
     * @deprecated Use {@see defineAppBootloaders()} method instead. Will be removed in v4.0
     */
    protected const APP = [];

    /** @var array<\Closure> */
    private array $bootingCallbacks = [];

    /** @var array<\Closure> */
    private array $bootedCallbacks = [];

    /**
     * Register a new callback, that will be fired before application bootloaders are booted.
     * (Before all application bootloaders will be booted)
     *
     * $kernel->appBooting(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     */
    public function appBooting(\Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->bootingCallbacks[] = $callback;
        }
    }

    /**
     * Register a new callback, that will be fired after application bootloaders are booted.
     * (After booting all application bootloaders)
     *
     * $kernel->booted(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     */
    public function appBooted(\Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->bootedCallbacks[] = $callback;
        }
    }

    /**
     * Get list of defined application bootloaders
     *
     * @return array<int, class-string>|array<class-string, array<non-empty-string, mixed>>
     *
     * @deprecated since v3.10 Use {@see defineBootloaders()} instead. Will be removed in v4.0
     */
    protected function defineAppBootloaders(): array
    {
        return static::APP;
    }

    /**
     * Each application can define it's own boot sequence.
     */
    protected function bootstrap(): void
    {
        $self = $this;
        $this->bootloader->bootload(
            $this->defineAppBootloaders(),
            [
                static function () use ($self): void {
                    $self->fireCallbacks($self->bootingCallbacks);
                },
            ]
        );

        $this->fireCallbacks($this->bootedCallbacks);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new BootException('Missing required directory `root`');
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return \array_merge(
            [
                // public root
                'public'    => $directories['root'] . '/public/',

                // vendor libraries
                'vendor'    => $directories['root'] . '/vendor/',

                // data directories
                'runtime'   => $directories['root'] . '/runtime/',
                'cache'     => $directories['root'] . '/runtime/cache/',

                // application directories
                'config'    => $directories['app'] . '/config/',
                'resources' => $directories['app'] . '/resources/',
            ],
            $directories
        );
    }
}
