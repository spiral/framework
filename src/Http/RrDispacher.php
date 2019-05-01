<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;
use Zend\Diactoros\Response;

final class RrDispacher implements DispatcherInterface
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
        return (php_sapi_name() == 'cli' && $this->env->get('RR_HTTP') !== null);
    }

    /**
     * @inheritdoc
     *
     * @param Worker|null $worker
     */
    public function serve(Worker $worker = null)
    {
        $client = new PSR7Client(
            $worker ?? $this->getWorker(),
            $this->container->get(RequestFactoryInterface::class)
        );

        /** @var HttpCore $http */
        $http = $this->container->get(HttpCore::class);
        while ($request = $client->acceptRequest()) {
            try {
                $client->respond($http->handle($request));
            } catch (\Throwable $e) {
                $this->handleException($client, $e);
            } finally {
                $this->finalizer->finalize(false);
            }
        }

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
     * @param PSR7Client $client
     * @param \Throwable $e
     */
    protected function handleException(PSR7Client $client, \Throwable $e)
    {
        $handler = new HtmlHandler(HtmlHandler::INVERTED);

        try {
            /** @var SnapshotInterface $snapshot */
            $snapshot = $this->container->get(SnapshotterInterface::class)->register($e);
            error_log($snapshot->getMessage());
        } catch (\Throwable|ContainerExceptionInterface $se) {
            error_log($handler->getMessage($e));
        }

        // Reporting system (non handled) exception directly to the client
        $response = new Response('php://memory', 500);
        $response->getBody()->write(
            $handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE)
        );

        $client->respond($response);
    }
}