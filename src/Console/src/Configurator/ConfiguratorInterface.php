<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;

/**
 * @internal
 */
interface ConfiguratorInterface
{
    public function canConfigure(Command $command, \ReflectionClass $reflection): bool;

    public function configure(Command $command, \ReflectionClass $reflection): void;
}
