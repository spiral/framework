<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Container;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Event\RequestHandled;
use Spiral\Http\Event\RequestReceived;
use Spiral\Http\Exception\HttpException;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerFactoryInterface;

final class Http implements RequestHandlerInterface
{
    private ?RequestHandlerInterface $handler = null;
    private readonly TracerFactoryInterface $tracerFactory;

    public function __construct(
        private readonly HttpConfig $config,
        private readonly Pipeline $pipeline,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ContainerInterface $container,
        ?TracerFactoryInterface $tracerFactory = null,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
        foreach ($this->config->getMiddleware() as $middleware) {
            $this->pipeline->pushMiddleware($this->container->get($middleware));
        }

        $scope = $this->container instanceof ScopeInterface ? $this->container : new Container();
        $this->tracerFactory = $tracerFactory ?? new NullTracerFactory($scope);
    }

    public function getPipeline(): Pipeline
    {
        return $this->pipeline;
    }

    public function setHandler(callable|RequestHandlerInterface $handler): self
    {
        $this->handler = $handler instanceof RequestHandlerInterface
            ? $handler
            : new CallableHandler($handler, $this->responseFactory);

        return $this;
    }

    /**
     * @throws HttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $callback = function (SpanInterface $span, CurrentRequest $currentRequest) use ($request): ResponseInterface {
            $currentRequest->set($request);

            $this->dispatcher?->dispatch(new RequestReceived($request));

            if ($this->handler === null) {
                throw new HttpException('Unable to run HttpCore, no handler is set.');
            }

            $response = $this->pipeline->withHandler($this->handler)->handle($request);

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

            $this->dispatcher?->dispatch(new RequestHandled($request, $response));

            return $response;
        };

        $tracer = $this->tracerFactory->make($request->getHeaders());

        /** @var ResponseInterface $response */
        $response = $tracer->trace(
            name: \sprintf('%s %s', $request->getMethod(), (string)$request->getUri()),
            callback: $callback,
            attributes: [
                'http.method' => $request->getMethod(),
                'http.url' => (string) $request->getUri(),
                'http.headers' => $request->getHeaders(),
            ],
            scoped: true,
            traceKind: TraceKind::SERVER,
        );

        foreach ($tracer->getContext() as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}
