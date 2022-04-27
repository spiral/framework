<?php

declare(strict_types=1);

namespace Spiral\Debug\StateCollector;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;

final class EnvironmentCollector implements StateCollectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EnvironmentInterface $env
    ) {
    }

    public function populate(StateInterface $state): void
    {
        $state->setTag('php', \phpversion());

        if ($this->container->has(DispatcherInterface::class)) {
            $state->setTag('dispatcher', $this->container->get(DispatcherInterface::class)::class);
        }

        $state->setVariable('environment', $this->env->getAll());
    }
}
