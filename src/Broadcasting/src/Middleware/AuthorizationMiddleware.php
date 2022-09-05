<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Event\AuthorizationFailed;
use Spiral\Broadcasting\Event\AuthorizationSuccess;
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

            if ($status->response !== null) {
                return $status->response;
            }

            if (!$status->success) {
                $this->dispatcher?->dispatch(new AuthorizationFailed($request));

                return $this->responseFactory->createResponse(403);
            }
        }

        $this->dispatcher?->dispatch(new AuthorizationSuccess($request));

        return $this->responseFactory->createResponse(200);
    }
}
