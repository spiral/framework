<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;

/**
 * @internal
 */
final class Configurator implements ConfiguratorInterface
{
    /**
     * @param ConfiguratorInterface[] $configurators
     */
    public function __construct(
        private readonly array $configurators = []
    ) {
    }

    public function configure(Command $command, \ReflectionClass $reflection): void
    {
        foreach ($this->configurators as $configurator) {
            if ($configurator->canConfigure($command, $reflection)) {
                $configurator->configure($command, $reflection);
                return;
            }
        }

        (new DefaultConfigurator())->configure($command, $reflection);
    }

    public function canConfigure(Command $command, \ReflectionClass $reflection): bool
    {
        return true;
    }
}
