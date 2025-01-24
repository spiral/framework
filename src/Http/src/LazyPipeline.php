<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Event\MiddlewareProcessing;
use Spiral\Http\Exception\PipelineException;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerInterface;

/**
 * Pipeline used to pass request and response thought the chain of middleware.
 * This kind of pipeline creates middleware on the fly.
 */
final class LazyPipeline implements RequestHandlerInterface, MiddlewareInterface
{
    /**
     * Set of middleware to be applied for every request.
     *
     * @var list<MiddlewareInterface|Autowire|string>
     */
    protected array $middleware = [];

    private ?RequestHandlerInterface $handler = null;
    private int $position = 0;

    /**
     * Trace span for the current pipeline run.
     */
    private ?SpanInterface $span = null;

    public function __construct(
        #[Proxy] private readonly ContainerInterface $container,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {}

    /**
     * Add middleware to the pipeline.
     *
     * @param MiddlewareInterface ...$middleware List of middleware or its definition.
     */
    public function withAddedMiddleware(MiddlewareInterface|Autowire|string ...$middleware): self
    {
        $pipeline = clone $this;
        $pipeline->middleware = \array_merge($pipeline->middleware, $middleware);
        return $pipeline;
    }

    /**
     * Replace middleware in the pipeline.
     *
     * @param MiddlewareInterface ...$middleware List of middleware or its definition.
     */
    public function withMiddleware(MiddlewareInterface|Autowire|string ...$middleware): self
    {
        $pipeline = clone $this;
        $pipeline->middleware = $middleware;
        return $pipeline;
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
        return $pipeline;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        return $this->withHandler($handler)->handle($request);
    }

    public function handle(Request $request): Response
    {
        $this->handler === null and throw new PipelineException('Unable to run pipeline, no handler given.');

        /** @var CurrentRequest $currentRequest */
        $currentRequest = $this->container->get(CurrentRequest::class);

        $previousRequest = $currentRequest->get();
        $currentRequest->set($request);
        try {
            // There is no middleware to process, let's pass the request to the handler
            if (!\array_key_exists($this->position, $this->middleware)) {
                return $this->handler->handle($request);
            }

            $middleware = $this->resolveMiddleware($this->position);
            $this->dispatcher?->dispatch(new MiddlewareProcessing($request, $middleware));

            $span = $this->span;

            $middlewareTitle = \is_string($this->middleware[$this->position])
            && $this->middleware[$this->position] !== $middleware::class
                ? \sprintf('%s=%s', $this->middleware[$this->position], $middleware::class)
                : $middleware::class;
            // Init a tracing span when the pipeline starts
            if ($span === null) {
                /** @var TracerInterface $tracer */
                $tracer = $this->container->get(TracerInterface::class);
                return $tracer->trace(
                    name: 'HTTP Pipeline',
                    callback: function (SpanInterface $span) use ($request, $middleware, $middlewareTitle): Response {
                        $span->setAttribute('http.middleware', [$middlewareTitle]);
                        return $middleware->process($request, $this->next($span));
                    },
                    scoped: true,
                );
            }

            $middlewares = $span->getAttribute('http.middleware') ?? [];
            $middlewares[] = $middlewareTitle;
            $span->setAttribute('http.middleware', $middlewares);

            return $middleware->process($request, $this->next($span));
        } finally {
            $currentRequest->set($previousRequest);
        }
    }

    private function next(SpanInterface $span): self
    {
        $pipeline = clone $this;
        ++$pipeline->position;
        $pipeline->span = $span;
        return $pipeline;
    }

    private function resolveMiddleware(int $position): MiddlewareInterface
    {
        $middleware = $this->middleware[$position];
        return $middleware instanceof MiddlewareInterface
            ? $middleware
            : $this->container->get($middleware);
    }
}
