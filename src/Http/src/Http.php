<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Event\RequestHandled;
use Spiral\Http\Event\RequestReceived;
use Spiral\Http\Exception\HttpException;

final class Http implements RequestHandlerInterface
{
    private ?RequestHandlerInterface $handler = null;

    public function __construct(
        private readonly HttpConfig $config,
        private readonly Pipeline $pipeline,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ContainerInterface $container
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
        $dispatcher = $this->container->has(EventDispatcherInterface::class)
            ? $this->container->get(EventDispatcherInterface::class)
            : null;


        $dispatcher?->dispatch(new RequestReceived($request));

        if ($this->handler === null) {
            throw new HttpException('Unable to run HttpCore, no handler is set.');
        }

        $response = $this->pipeline->withHandler($this->handler)->handle($request);

        $dispatcher?->dispatch(new RequestHandled($request, $response));

        return $response;
    }
}
