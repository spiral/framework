<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
 */
abstract class Bootloader implements BootloaderInterface, DependedInterface
{
    protected const BINDINGS     = [];
    protected const SINGLETONS   = [];
    protected const DEPENDENCIES = [];

    /**
     * {@inheritdoc}
     */
    public function defineBindings(): array
    {
        return static::BINDINGS;
    }

    /**
     * {@inheritdoc}
     */
    public function defineSingletons(): array
    {
        return static::SINGLETONS;
    }

    /**
     * {@inheritdoc}
     */
    public function defineDependencies(): array
    {
        return static::DEPENDENCIES;
    }
}
