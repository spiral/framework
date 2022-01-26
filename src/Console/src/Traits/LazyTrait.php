<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Vladislav Gorenkin (vladgorenkin)
 */

declare(strict_types=1);

namespace Spiral\Console\Traits;

use Psr\Container\ContainerInterface;
use Spiral\Console\Command as SpiralCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Command\LazyCommand;

trait LazyTrait
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Check if command can be lazy-loaded.
     *
     * @param class-string $class
     */
    private function supportsLazyLoading(string $class): bool
    {
        return is_subclass_of($class, SpiralCommand::class)
            && \defined(sprintf('%s::%s', $class, 'NAME'));
    }

    /**
     * Wrap given command into LazyCommand which will be executed only on run.
     *
     * @param class-string $class
     */
    private function createLazyCommand(string $class): LazyCommand
    {
        return new LazyCommand(
            $class::NAME,
            [],
            \defined(sprintf('%s::%s', $class, 'DESCRIPTION'))
                ? $class::DESCRIPTION
                : '',
            false,
            function () use ($class): SymfonyCommand {
                $command = $this->container->get($class);
                $command->setContainer($this->container);

                return $command;
            }
        );
    }
}
