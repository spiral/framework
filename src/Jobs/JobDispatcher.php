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
use Spiral\Goridge\StreamRelay;
use Spiral\Jobs\Factory\SpiralFactory;
use Spiral\RoadRunner\Worker;
use Spiral\Snapshots\SnapshotterInterface;

final class JobDispatcher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var FinalizerInterface */
    private $finalizer;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param EnvironmentInterface $environment
     * @param FinalizerInterface   $finalizer
     * @param ContainerInterface   $container
     */
    public function __construct(
        EnvironmentInterface $environment,
        FinalizerInterface $finalizer,
        ContainerInterface $container
    ) {
        $this->environment = $environment;
        $this->finalizer = $finalizer;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        return (php_sapi_name() == 'cli' && $this->environment->get('RR_JOBS') !== null);
    }

    /**
     * @inheritdoc
     */
    public function serve()
    {
        $consumer = new Consumer(
            $this->getWorker(),
            new SpiralFactory($this->container->get(\Spiral\Core\FactoryInterface::class))
        );

        $consumer->serve(function (\Throwable $e = null) {
            if ($e !== null) {
                $this->handleException($e);
            }

            $this->finalizer->finalize(false);
        });

        $this->finalizer->finalize(true);
    }

    /**
     * @return Worker
     */
    protected function getWorker(): Worker
    {
        return new Worker(new StreamRelay(STDIN, STDOUT));
    }

    /**
     * @param \Throwable $e
     */
    protected function handleException(\Throwable $e)
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable|ContainerExceptionInterface $se) {
            // no need to notify when unable to register an exception
        }
    }
}