<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * Provides ability to initiate set of container bindings using simple string form without closures.
 *
 * You can make any initializer automatically bootable by defining boot() method with
 * automatically resolved arguments.
 *
 * Attention, you are able to define your own set of shared (short bindings) components in your
 * bootloader, DO NOT share your business models this way - use regular DI.
 *
 * @phpstan-import-type TConstantBinding from BootloaderInterface
 */
abstract class Bootloader implements BootloaderInterface, DependedInterface
{
    /**
     * @var array<string, class-string|callable>
     * @psalm-var TConstantBinding
     */
    protected const BINDINGS = [];
    /**
     * @var array<string, class-string|callable>
     * @psalm-var TConstantBinding
     */
    protected const SINGLETONS = [];
    /** @var class-string<BootloaderInterface>[]|class-string<DependedInterface>[] */
    protected const DEPENDENCIES = [];

    public function defineBindings(): array
    {
        return static::BINDINGS;
    }

    public function defineSingletons(): array
    {
        return static::SINGLETONS;
    }

    public function defineDependencies(): array
    {
        return static::DEPENDENCIES;
    }
}
