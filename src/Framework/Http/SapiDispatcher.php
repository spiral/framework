<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Verbosity;
use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

final class SapiDispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly FinalizerInterface $finalizer,
        private readonly ContainerInterface $container
    ) {
    }

    public function canServe(): bool
    {
        return PHP_SAPI !== 'cli';
    }

    public function serve(EmitterInterface $emitter = null): void
    {
        // On demand to save some memory.

        /**
         * @var Http             $http
         * @var EmitterInterface $emitter
         */
        $http = $this->container->get(Http::class);
        $emitter = $emitter ?? $this->container->get(EmitterInterface::class);

        try {
            $response = $http->handle($this->initRequest());
            $emitter->emit($response);
        } catch (\Throwable $e) {
            $this->handleException($emitter, $e);
        } finally {
            $this->finalizer->finalize(false);
        }
    }

    protected function initRequest(): ServerRequestInterface
    {
        return $this->container->get(SapiRequestFactory::class)->fromGlobals();
    }

    protected function handleException(EmitterInterface $emitter, \Throwable $e): void
    {
        $handler = new HtmlRenderer();

        try {
            /** @var SnapshotInterface $snapshot */
            $this->container->get(SnapshotterInterface::class)->register($e);

            // on demand
            $state = $this->container->get(StateInterface::class);
            if ($state !== null) {
                $handler = $handler->withState($state);
            }
        } catch (\Throwable) {
            // nothing to report
        }

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse(500);

        // Reporting system (non handled) exception directly to the client
        $response->getBody()->write(
            $handler->render($e, verbosity: Verbosity::VERBOSE)
        );

        $emitter->emit($response);
    }
}
