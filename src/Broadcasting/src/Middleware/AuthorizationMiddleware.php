<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\GuardInterface;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private BroadcastInterface $broadcast;
    private ?string $authorizationPath;

    public function __construct(
        BroadcastInterface $broadcast,
        ResponseFactoryInterface $responseFactory,
        ?string $authorizationPath = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->broadcast = $broadcast;
        $this->authorizationPath = $authorizationPath;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getUri()->getPath() !== $this->authorizationPath) {
            return $handler->handle($request);
        }

        if ($this->broadcast instanceof GuardInterface) {
            return $this->broadcast->authorize($request);
        }

        return $this->responseFactory->createResponse(200);
    }
}
