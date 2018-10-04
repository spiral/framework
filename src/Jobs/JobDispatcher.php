<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Jobs;

use Doctrine\Common\Inflector\Inflector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Exceptions\ConsoleHandler;
use Spiral\Goridge\StreamRelay;
use Spiral\Jobs\Exception\JobException;
use Spiral\RoadRunner\Worker;
use Spiral\Snapshots\SnapshotterInterface;

class JobDispatcher implements DispatcherInterface
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
        $worker = new Worker(new StreamRelay(STDIN, STDOUT));

        while ($body = $worker->receive($context)) {
            try {
                $context = json_decode($context, true);

                $job = $this->getJob($context['job']);
                $job->unserialize($body);
                $job->execute($context['id']);

                $worker->send("ok");
            } catch (\Throwable $e) {
                $this->handleException($worker, $e);
            } finally {
                $this->finalizer->finalize();
            }
        }
    }

    /**
     * @param string $job
     * @return JobInterface
     */
    protected function getJob(string $job): JobInterface
    {
        $names = explode('.', $job);
        $names = array_map(function (string $value) {
            return Inflector::classify($value);
        }, $names);

        try {
            return $this->container->get(join('\\', $names));
        } catch (ContainerExceptionInterface $e) {
            throw new JobException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Worker     $worker
     * @param \Throwable $e
     */
    protected function handleException(Worker $worker, \Throwable $e)
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable|ContainerExceptionInterface $se) {
            // no need to notify when unable to register an exception
        }

        // Explaining exception to the user
        $handler = new ConsoleHandler(STDERR);
        $worker->error($handler->renderException($e, ConsoleHandler::VERBOSITY_VERBOSE));
    }
}