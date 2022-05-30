<?php

declare(strict_types=1);

namespace Spiral\Framework;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Exception\BootException;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

abstract class Kernel extends AbstractKernel
{
    // framework specific bootloaders
    protected const SYSTEM = [
        CoreBootloader::class,
        TokenizerBootloader::class,
    ];

    // application specific bootloaders
    protected const APP = [];

    /**
     * Each application can define it's own boot sequence.
     */
    protected function bootstrap(): void
    {
        $self = $this;
        $this->bootloader->bootload(static::APP,
            [
                static function () use ($self): void {
                    $self->fireCallbacks($self->appBootingCallbacks);
                },
            ]);

        $this->fireCallbacks($this->appBootedCallbacks);
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
