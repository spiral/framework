<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class CommandLocator implements LocatorInterface
{
    use LazyTrait;

    public function __construct(
        private readonly ScopedClassesInterface $classes,
        private ConsoleConfig $config,
        ContainerInterface $container,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->container = $container;
        $this->dispatcher = $eventDispatcher;
    }

    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->classes->getScopedClasses('consoleCommands', SymfonyCommand::class) as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            $commands[] = $command = $this->supportsLazyLoading($class->getName())
                ? $this->createLazyCommand($class->getName())
                : $this->container->get($class->getName());

            if ($this->dispatcher !== null && $command instanceof EventDispatcherAwareInterface) {
                $command->setEventDispatcher($this->dispatcher);
            }
        }

        return $commands;
    }
}
