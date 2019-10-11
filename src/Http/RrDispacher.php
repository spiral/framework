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
use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\RoadRunner\PSR7Client;
use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

final class RrDispacher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $env;

    /** @var FinalizerInterface */
    private $finalizer;

    /** @var ContainerInterface */
    private $container;

    /** @var FactoryInterface */
    private $factory;

    /**
     * @param EnvironmentInterface $env
     * @param FinalizerInterface   $finalizer
     * @param ContainerInterface   $container
     * @param FactoryInterface     $factory
     */
    public function __construct(
        EnvironmentInterface $env,
        FinalizerInterface $finalizer,
        ContainerInterface $container,
        FactoryInterface $factory
    ) {
        $this->env = $env;
        $this->finalizer = $finalizer;
        $this->container = $container;
        $this->factory = $factory;
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
     */
    public function serve(PSR7Client $client = null): void
    {
        $client = $client ?? $this->factory->make(PSR7Client::class);

        /** @var Http $http */
        $http = $this->container->get(Http::class);
        while ($request = $client->acceptRequest()) {
            try {
                $client->respond($http->handle($request));
            } catch (\Throwable $e) {
                $this->handleException($client, $e);
            } finally {
                $this->finalizer->finalize(false);
            }
        }
    }

    /**
     * @param PSR7Client $client
     * @param \Throwable $e
     */
    protected function handleException(PSR7Client $client, \Throwable $e): void
    {
        $handler = new HtmlHandler();

        try {
            /** @var SnapshotInterface $snapshot */
            $snapshot = $this->container->get(SnapshotterInterface::class)->register($e);
            error_log($snapshot->getMessage());
        } catch (\Throwable | ContainerExceptionInterface $se) {
            error_log((string)$e);
        }

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse(500);

        // Reporting system (non handled) exception directly to the client
        $response->getBody()->write(
            $handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE)
        );

        $client->respond($response);
    }
}
