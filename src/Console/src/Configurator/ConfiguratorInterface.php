<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;

/**
 * @internal
 */
interface ConfiguratorInterface
{
    /**
     * Check if a given command can be configured by the configurator.
     *
     * @param \ReflectionClass<Command> $reflection
     */
    public function canConfigure(Command $command, \ReflectionClass $reflection): bool;

    /**
     * Configure a given command.
     *
     * @param \ReflectionClass<Command> $reflection
     */
    public function configure(Command $command, \ReflectionClass $reflection): void;
}
