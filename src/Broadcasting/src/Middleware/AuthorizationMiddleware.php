<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\AuthorizationStatus;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Event\Authorized;
use Spiral\Broadcasting\GuardInterface;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly BroadcastInterface $broadcast,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ?string $authorizationPath = null,
        private readonly ?EventDispatcherInterface $dispatcher = null
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
        } else {
            $status = new AuthorizationStatus(
                success: true,
                topics: null
            );
        }

        $this->dispatcher?->dispatch(new Authorized($status, $request));

        if ($status->response !== null) {
            return $status->response;
        }

        if (!$status->success) {
            return $this->responseFactory->createResponse(403);
        }

        return $this->responseFactory->createResponse(200);
    }
}
