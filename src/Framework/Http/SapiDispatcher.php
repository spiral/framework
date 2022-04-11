<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Exceptions\ErrorHandlerInterface;
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Verbosity;

final class SapiDispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly FinalizerInterface $finalizer,
        private readonly ContainerInterface $container,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function canServe(): bool
    {
        return PHP_SAPI !== 'cli';
    }

    public function serve(EmitterInterface $emitter = null): void
    {
        // On demand to save some memory.
        $http = $this->container->get(Http::class);
        $emitter = $emitter ?? $this->container->get(EmitterInterface::class);

        try {
            echo \count($http->getPipeline()->middleware) . ":<br>\n";
            foreach ($http->getPipeline()->middleware as $mw) {
                echo $mw::class . "<br>\n";
            }
            echo 'Handler: ' . $http->handler::class . "<br>\n";
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
        $handler = $this->errorHandler->getRenderer('html') ?? new HtmlRenderer();
        $this->errorHandler->report($e);

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
