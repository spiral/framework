<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\ContainerScope;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Event\MiddlewareProcessing;
use Spiral\Http\Exception\PipelineException;
use Spiral\Http\Traits\MiddlewareTrait;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerInterface;

/**
 * Pipeline used to pass request and response thought the chain of middleware.
 */
final class Pipeline implements RequestHandlerInterface, MiddlewareInterface
{
    use MiddlewareTrait;

    private int $position = 0;
    private readonly TracerInterface $tracer;
    private ?RequestHandlerInterface $handler = null;

    public function __construct(
        #[Proxy] private readonly ScopeInterface $scope,
        private readonly ?EventDispatcherInterface $dispatcher = null,
        ?TracerInterface $tracer = null
    ) {
        $this->tracer = $tracer ?? new NullTracer($scope);
    }

    /**
     * Configures pipeline with target endpoint.
     *
     * @throws PipelineException
     */
    public function withHandler(RequestHandlerInterface $handler): self
    {
        $pipeline = clone $this;
        $pipeline->handler = $handler;
        $pipeline->position = 0;

        return $pipeline;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        return $this->withHandler($handler)->handle($request);
    }

    public function handle(Request $request): Response
    {
        if ($this->handler === null) {
            throw new PipelineException('Unable to run pipeline, no handler given.');
        }

        // todo: find a better solution in the Spiral v4.0
        /** @var CurrentRequest|null $currentRequest */
        $currentRequest = ContainerScope::getContainer()?->get(CurrentRequest::class);

        $previousRequest = $currentRequest?->get();
        $currentRequest?->set($request);
        try {
            $position = $this->position++;
            if (!isset($this->middleware[$position])) {
                return $this->handler->handle($request);
            }

            $middleware = $this->middleware[$position];
            $this->dispatcher?->dispatch(new MiddlewareProcessing($request, $middleware));

            $callback = function (SpanInterface $span) use ($request, $middleware): Response {
                $response = $middleware->process($request, $this);

                $span
                    ->setAttribute(
                        'http.status_code',
                        $response->getStatusCode()
                    )
                    ->setAttribute(
                        'http.response_content_length',
                        $response->getHeaderLine('Content-Length') ?: $response->getBody()->getSize()
                    )
                    ->setStatus($response->getStatusCode() < 500 ? 'OK' : 'ERROR');

                return $response;
            };

            return $this->tracer->trace(
                name: \sprintf('Middleware processing [%s]', $middleware::class),
                callback: $callback,
                attributes: [
                    'http.middleware' => $middleware::class,
                ],
                scoped: true
            );
        } finally {
            if ($previousRequest !== null) {
                $currentRequest?->set($previousRequest);
            }
        }
    }
}
