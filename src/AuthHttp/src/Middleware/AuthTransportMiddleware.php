<?php

declare(strict_types=1);

namespace Spiral\Auth\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\ScopeInterface;

/**
 * Auth by specific transport.
 */
final class AuthTransportMiddleware implements MiddlewareInterface
{
    private readonly AuthMiddleware $authMiddleware;

    /**
     * @param ScopeInterface $scope Deprecated, will be removed in v4.0.
     */
    public function __construct(
        string $transportName,
        ScopeInterface $scope,
        ActorProviderInterface $actorProvider,
        TokenStorageInterface $tokenStorage,
        TransportRegistry $transportRegistry,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->authMiddleware = new AuthMiddleware(
            $scope,
            $actorProvider,
            $tokenStorage,
            $this->getTransportRegistry($transportRegistry, $transportName),
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

    private function getTransportRegistry(TransportRegistry $registry, string $transportName): TransportRegistry
    {
        $transports = new TransportRegistry();
        $transports->setDefaultTransport($transportName);
        $transports->setTransport($transportName, $registry->getTransport($transportName));

        return $transports;
    }
}
