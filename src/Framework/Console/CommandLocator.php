<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class CommandLocator implements LocatorInterface
{
    use LazyTrait;

    public function __construct(
        private readonly ScopedClassesInterface $classes,
        private ConsoleConfig $config,
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->classes->getScopedClasses('consoleCommands', SymfonyCommand::class) as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            $commands[] = $this->supportsLazyLoading($class->getName())
                ? $this->createLazyCommand($class->getName())
                : $this->container->get($class->getName());
        }

        return $commands;
    }
}
