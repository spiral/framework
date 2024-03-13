<?php

declare(strict_types=1);

namespace Spiral\Auth\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenStorageProviderInterface;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\ScopeInterface;

/**
 * Auth by specific transport.
 */
final class AuthTransportWithStorageMiddleware implements MiddlewareInterface
{
    private readonly MiddlewareInterface $authMiddleware;

    /**
     * @param ScopeInterface $scope Deprecated, will be removed in v4.0.
     */
    public function __construct(
        string $transportName,
        ScopeInterface $scope,
        ActorProviderInterface $actorProvider,
        TokenStorageProviderInterface $tokenStorageProvider,
        TransportRegistry $transportRegistry,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?string $storage = null
    ) {
        $this->authMiddleware = new AuthTransportMiddleware(
            $transportName,
            $scope,
            $actorProvider,
            $tokenStorageProvider->getStorage($storage),
            $transportRegistry,
            $eventDispatcher
        );
    }

    /**
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->authMiddleware->process($request, $handler);
    }
}
