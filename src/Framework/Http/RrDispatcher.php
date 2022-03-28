<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
class RrDispatcher implements DispatcherInterface
{
    /**
     * @var EnvironmentInterface
     */
    private $env;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FinalizerInterface
     */
    private $finalizer;

    /**
     * @param EnvironmentInterface $env
     * @param ContainerInterface $container
     * @param FinalizerInterface $finalizer
     */
    public function __construct(
        EnvironmentInterface $env,
        ContainerInterface $container,
        FinalizerInterface $finalizer
    ) {
        $this->env = $env;
        $this->container = $container;
        $this->finalizer = $finalizer;
    }

    /**
     * @return bool
     */
    public function canServe(): bool
    {
        return \PHP_SAPI === 'cli' && $this->env->getMode() === Mode::MODE_HTTP;
    }

    /**
     * @return void
     */
    public function serve(): void
    {
        /** @var PSR7WorkerInterface $worker */
        $worker = $this->container->get(PSR7WorkerInterface::class);

        /** @var Http $http */
        $http = $this->container->get(Http::class);

        while ($request = $worker->waitRequest()) {
            try {
                $response = $http->handle($request);

                $worker->respond($response);
            } catch (\Throwable $e) {
                $worker->respond($this->errorToResponse($e));
            } finally {
                $this->finalizer->finalize(false);
            }
        }
    }

    /**
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected function errorToResponse(\Throwable $e): ResponseInterface
    {
        $handler = new HtmlHandler();

        try {
            /** @var SnapshotInterface $snapshot */
            $snapshot = $this->container->get(SnapshotterInterface::class)->register($e);
            \file_put_contents('php://stderr', $snapshot->getMessage());

            // on demand
            $state = $this->container->get(StateInterface::class);

            if ($state !== null) {
                $handler = $handler->withState($state);
            }
        } catch (\Throwable | ContainerExceptionInterface $se) {
            \file_put_contents('php://stderr', (string)$e);
        }

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse(500);

        // Reporting system (non handled) exception directly to the client
        $response->getBody()->write(
            $handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE)
        );

        return $response;
    }
}
