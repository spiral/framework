<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * @psalm-type TClass = class-string<BootloaderInterface>
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
    public function addSystemBootloader(string|array $bootloader): void;

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     *
     * Examples:
     *  1. SimpleBootloader::class,
     *  2. [SimpleBootloader::class => ['option' => 'value']]
     */
    public function addLoadBootloader(string|array $bootloader): void;

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     *
     * Examples:
     *  1. SimpleBootloader::class,
     *  2. [SimpleBootloader::class => ['option' => 'value']]
     */
    public function addApplicationBootloader(string|array $bootloader): void;

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getSystemBootloaders(): array;

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getLoadBootloaders(): array;

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getApplicationBootloaders(): array;
}
