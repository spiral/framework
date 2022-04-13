<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Config\BroadcastConfig;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    private BroadcastConfig $config;
    private ResponseFactoryInterface $responseFactory;
    private BroadcastInterface $broadcast;

    public function __construct(
        BroadcastInterface $broadcast,
        BroadcastConfig $config,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->config = $config;
        $this->responseFactory = $responseFactory;
        $this->broadcast = $broadcast;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getUri()->getPath() !== $this->config->getAuthorizationPath()) {
            return $handler->handle($request);
        }

        if ($this->broadcast->authorize($request)) {
            return $this->responseFactory->createResponse(200);
        }

        return $this->responseFactory->createResponse(403);
    }
}
