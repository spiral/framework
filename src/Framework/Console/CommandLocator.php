<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class CommandLocator implements LocatorInterface
{
    use LazyTrait;

    /**
     * @param array<class-string<CoreInterceptorInterface|InterceptorInterface>> $interceptors
     */
    public function __construct(
        private readonly ScopedClassesInterface $classes,
        ContainerInterface $container,
        array $interceptors = [],
    ) {
        $this->container = $container;
        $this->interceptors = $interceptors;
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
