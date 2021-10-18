<?php

declare(strict_types=1);

namespace Spiral\Jobs;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\Snapshots\SnapshotterInterface;

final class JobDispatcher implements DispatcherInterface
{
    /**
     * @var EnvironmentInterface
     */
    private EnvironmentInterface $env;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var FinalizerInterface
     */
    private FinalizerInterface $finalizer;

    /**
     * @param ContainerInterface $container
     * @param FinalizerInterface $finalizer
     * @param EnvironmentInterface $env
     */
    public function __construct(
        ContainerInterface $container,
        FinalizerInterface $finalizer,
        EnvironmentInterface $env
    ) {
        $this->env = $env;
        $this->container = $container;
        $this->finalizer = $finalizer;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        return \PHP_SAPI == 'cli' && $this->env->getMode() === Mode::MODE_JOBS;
    }

    /**
     * @inheritdoc
     */
    public function serve(): void
    {
        /** @var ConsumerInterface $consumer */
        $consumer = $this->container->get(ConsumerInterface::class);

        while ($task = $consumer->waitTask()) {
            try {
                $instance = $this->container->get($task->getName());

                if (!$instance instanceof HandlerInterface) {
                    throw new \LogicException(
                        \sprintf('Job should be an instance %s', HandlerInterface::class)
                    );
                }

                $instance->handle($task->getName(), $task->getId(), $task->getPayload());
                $task->complete();
            } catch (\Throwable $e) {
                $this->handleException($e);
                $task->fail($e);
            }

            $this->finalizer->finalize(false);
        }
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
