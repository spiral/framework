<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * @psalm-import-type TClass from \Spiral\Boot\BootloadManagerInterface
 */
interface BootloaderRegistryInterface
{
    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     *
     * Examples:
     *  1. SimpleBootloader::class,
     *  2. [SimpleBootloader::class => ['option' => 'value']]
     */
    public function registerSystem(string|array $bootloader): void;

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     *
     * Examples:
     *  1. SimpleBootloader::class,
     *  2. [SimpleBootloader::class => ['option' => 'value']]
     */
    public function register(string|array $bootloader): void;

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getSystemBootloaders(): array;

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getBootloaders(): array;
}
