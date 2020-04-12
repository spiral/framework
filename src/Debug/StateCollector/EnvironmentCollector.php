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
use Spiral\GRPC\GRPCDispatcher;
use Spiral\Http\RrDispatcher;
use Spiral\Http\SapiDispatcher;
use Spiral\Jobs\JobDispatcher;

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

        if ($this->container->get(DispatcherInterface::class)) {
            switch (get_class($this->container->get(DispatcherInterface::class))) {
                case RrDispatcher::class:
                    $state->setTag('dispatcher', 'roadrunner');
                    break;
                case SapiDispatcher::class:
                    $state->setTag('dispatcher', 'sapi');
                    break;
                case JobDispatcher::class:
                    $state->setTag('dispatcher', 'jobs');
                    break;
                case GRPCDispatcher::class:
                    $state->setTag('dispatcher', 'grpc');
                    break;
            }
        }

        $state->setVariable('environment', $this->env->getAll());
    }
}
