<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\RoadRunner;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Goridge\StreamRelay;
use Spiral\Http\HttpCore;
use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;
use Zend\Diactoros\Response;

class RoadRunnerDispatcher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var ContainerInterface */
    private $container;

    /** @var callable[] */
    private $finalizers = [];

    /**
     * @param EnvironmentInterface $environment
     * @param ContainerInterface   $container
     */
    public function __construct(EnvironmentInterface $environment, ContainerInterface $container)
    {
        $this->environment = $environment;
        $this->container = $container;
    }

    /**
     * Finalizers are executed after every request and used for garbage collection
     * or to close open connections.
     *
     * @param callable $finalizer
     */
    public function addFinalizer(callable $finalizer)
    {
        $this->finalizers[] = $finalizer;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        return (php_sapi_name() == 'cli' && $this->environment->get('RR_HTTP') !== null);
    }

    /**
     * @inheritdoc
     */
    public function serve()
    {
        /** @var HttpCore $http */
        $http = $this->container->get(HttpCore::class);
        $client = $this->psr7Client();

        while ($request = $client->acceptRequest()) {
            try {
                $client->respond($http->handle($request));
            } catch (\Throwable $e) {
                $this->handleException($client, $e);
            } finally {
                foreach ($this->finalizers as $finalizer) {
                    call_user_func($finalizer);
                }
            }
        }
    }

    /**
     * @return PSR7Client
     */
    protected function psr7Client(): PSR7Client
    {
        $worker = new Worker(new StreamRelay(STDIN, STDOUT));

        return new PSR7Client($worker);
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