<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class CommandLocator implements LocatorInterface
{
    use LazyTrait;

    private ScopedClassesInterface $classes;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ScopedClassesInterface $classes, ContainerInterface $container)
    {
        $this->classes = $classes;
        $this->container = $container;
    }

    /**
     * @return array
     */
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
