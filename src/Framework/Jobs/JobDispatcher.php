<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\RoadRunner\Worker;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * @deprecated since 2.9. Will be removed since 3.0
 */
final class JobDispatcher implements DispatcherInterface
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
        return (php_sapi_name() == 'cli' && $this->env->get('RR_JOBS') !== null);
    }

    /**
     * @inheritdoc
     */
    public function serve(): void
    {
        // On demand to save some memory.

        /**
         * @var Consumer $consumer
         * @var Worker   $worker
         */
        $consumer = $this->container->get(Consumer::class);
        $worker = $this->container->get(Worker::class);

        $consumer->serve(
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
