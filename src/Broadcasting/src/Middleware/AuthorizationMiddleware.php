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
    public function __construct(
        private readonly BroadcastInterface $broadcast,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ?string $authorizationPath = null
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getUri()->getPath() !== $this->authorizationPath) {
            return $handler->handle($request);
        }

        if ($this->broadcast instanceof GuardInterface) {
            $status = $this->broadcast->authorize($request);

            if ($status->hasResponse()) {
                return $status->getResponse();
            }

            if (!$status->isSuccessful()) {
                return $this->responseFactory->createResponse(403);
            }
        }

        return $this->responseFactory->createResponse(200);
    }
}
