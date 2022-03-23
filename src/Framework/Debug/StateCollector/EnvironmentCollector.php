<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\StateCollector;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;
use Spiral\Http\SapiDispatcher;

final class EnvironmentCollector implements StateCollectorInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var EnvironmentInterface */
    private $env;

    /**
     * @param ContainerInterface   $container
     * @param EnvironmentInterface $env
     */
    public function __construct(ContainerInterface $container, EnvironmentInterface $env)
    {
        $this->container = $container;
        $this->env = $env;
    }

    public function populate(StateInterface $state): void
    {
        $state->setTag('php', phpversion());

        if ($this->container->has(DispatcherInterface::class)) {
            switch (get_class($this->container->get(DispatcherInterface::class))) {
                case SapiDispatcher::class:
                    $state->setTag('dispatcher', 'sapi');
                    break;
            }
        }

        $state->setVariable('environment', $this->env->getAll());
    }
}
