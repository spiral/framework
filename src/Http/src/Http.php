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
use Spiral\Telemetry\TracerFactory;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;

final class Http implements RequestHandlerInterface
{
    private ?RequestHandlerInterface $handler = null;

    public function __construct(
        private readonly HttpConfig $config,
        private readonly Pipeline $pipeline,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ContainerInterface $container,
        private readonly ?ScopeInterface $scope = new Container(),
        private readonly ?TracerFactoryInterface $tracerFactory = new TracerFactory()
    ) {
        foreach ($this->config->getMiddleware() as $middleware) {
            $this->pipeline->pushMiddleware($this->container->get($middleware));
        }
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
        $callback = function (SpanInterface $span) use ($request): ResponseInterface {
            $dispatcher = $this->container->has(EventDispatcherInterface::class)
                ? $this->container->get(EventDispatcherInterface::class)
                : null;

            $dispatcher?->dispatch(new RequestReceived($request));

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

            $dispatcher?->dispatch(new RequestHandled($request, $response));

            return $response;
        };

        $tracer = $this->tracerFactory->fromContext($request->getHeaders());

        return $this->scope->runScope([
            TracerInterface::class => $tracer,
        ], static function () use ($callback, $request, $tracer): ResponseInterface {
            /** @var ResponseInterface $response */
            $response = $tracer->trace(
                name: \sprintf('%s %s', $request->getMethod(), (string)$request->getUri()),
                callback: $callback,
                attributes: [
                    'http.method' => $request->getMethod(),
                    'http.url' => $request->getUri(),
                    'http.headers' => $request->getHeaders(),
                ],
                scoped: true,
                traceKind: TraceKind::SERVER
            );

            $context = $tracer->getContext();

            if ($context !== null) {
                foreach ($context as $key => $value) {
                    $response = $response->withHeader($key, $value);
                }
            }

            return $response;
        });
    }
}
