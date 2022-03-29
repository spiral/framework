<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Core\Container;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class StaticLocator implements LocatorInterface
{
    use LazyTrait;

    /**
     * @param array<array-key, class-string<SymfonyCommand>> $commands
     */
    public function __construct(
        private array $commands,
        private ContainerInterface $container = new Container()
    ) {
    }

    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[] = $this->supportsLazyLoading($command)
                ? $this->createLazyCommand($command)
                : $this->container->get($command);
        }

        return $commands;
    }
}
