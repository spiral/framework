<?php

declare(strict_types=1);

namespace Spiral\Console\Traits;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Command as SpiralCommand;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Events\EventDispatcherAwareInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Command\LazyCommand;

trait LazyTrait
{
    private ContainerInterface $container;
    private ConsoleConfig $config;
    private ?EventDispatcherInterface $dispatcher = null;

    /**
     * Check if command can be lazy-loaded.
     *
     * @param class-string $class
     */
    private function supportsLazyLoading(string $class): bool
    {
        return \is_subclass_of($class, SpiralCommand::class)
            && \defined(\sprintf('%s::%s', $class, 'NAME'));
    }

    /**
     * Wrap given command into LazyCommand which will be executed only on run.
     *
     * @param class-string<SpiralCommand> $class
     */
    private function createLazyCommand(string $class): LazyCommand
    {
        return new LazyCommand(
            $class::NAME,
            [],
            \defined(\sprintf('%s::%s', $class, 'DESCRIPTION'))
                ? $class::DESCRIPTION
                : '',
            false,
            function () use ($class): SymfonyCommand {
                $command = $this->container->get($class);
                $command->setContainer($this->container);
                $command->setInterceptors($this->config->getInterceptors());

                if ($this->dispatcher !== null && $command instanceof EventDispatcherAwareInterface) {
                    $command->setEventDispatcher($this->dispatcher);
                }

                return $command;
            }
        );
    }
}
