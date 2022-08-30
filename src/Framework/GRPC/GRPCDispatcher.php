<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\GRPC;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\RoadRunner\Worker;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class GRPCDispatcher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $env;

    /** @var FinalizerInterface */
    private $finalizer;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param EnvironmentInterface $env
     * @param FinalizerInterface   $finalizer
     * @param ContainerInterface   $container
     */
    public function __construct(
        EnvironmentInterface $env,
        FinalizerInterface $finalizer,
        ContainerInterface $container
    ) {
        $this->env = $env;
        $this->finalizer = $finalizer;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        return (php_sapi_name() === 'cli' && $this->env->get('RR_GRPC') !== null);
    }

    /**
     * @inheritdoc
     */
    public function serve(): void
    {
        // On demand to save some memory.

        /**
         * @var Server           $server
         * @var Worker           $worker
         * @var LocatorInterface $locator
         */
        $server = $this->container->get(Server::class);
        $worker = $this->container->get(Worker::class);
        $locator = $this->container->get(LocatorInterface::class);

        foreach ($locator->getServices() as $interface => $service) {
            $server->registerService($interface, $service);
        }

        $server->serve(
            $worker,
            function (\Throwable $e = null): void {
                if ($e !== null) {
                    $this->handleException($e);
                }

                $this->finalizer->finalize(false);
            }
        );
    }

    /**
     * @param \Throwable $e
     */
    protected function handleException(\Throwable $e): void
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable | ContainerExceptionInterface $se) {
            // no need to notify when unable to register an exception
        }
    }
}
