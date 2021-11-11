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
use Spiral\Core\Container;

final class StaticLocator implements LocatorInterface
{
    /** @var []string */
    private $commands;

    /** @var ContainerInterface */
    private $factory;

    public function __construct(array $commands, ContainerInterface $container = null)
    {
        $this->commands = $commands;
        $this->factory = $container ?? new Container();
    }

    /**
     * {@inheritdoc}
     */
    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[] = $this->factory->get($command);
        }

        return $commands;
    }
}
