<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Core\Container;
use Spiral\Events\EventDispatcherAwareInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class StaticLocator implements LocatorInterface
{
    use LazyTrait;

    /**
     * @param array<array-key, class-string<SymfonyCommand>> $commands
     */
    public function __construct(
        private readonly array $commands,
        private ConsoleConfig $config,
        ContainerInterface $container = new Container(),
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->dispatcher = $eventDispatcher;
        $this->container = $container;
    }

    /**
     * @return SymfonyCommand[]
     */
    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->commands as $command) {
            if ($command instanceof SymfonyCommand) {
                $commands[] = $command;
                continue;
            }

            $commands[] = $command = $this->supportsLazyLoading($command)
                ? $this->createLazyCommand($command)
                : $this->container->get($command);

            if ($this->dispatcher !== null && $command instanceof EventDispatcherAwareInterface) {
                $command->setEventDispatcher($this->dispatcher);
            }
        }

        return $commands;
    }
}
