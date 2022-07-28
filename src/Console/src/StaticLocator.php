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
use Spiral\Core\Container;

final class StaticLocator implements LocatorInterface
{
    use LazyTrait;

    /** @var string[] */
    private array $commands;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $commands, ContainerInterface $container = null)
    {
        $this->commands = $commands;
        $this->container = $container ?? new Container();
    }

    /**
     * {@inheritdoc}
     */
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
