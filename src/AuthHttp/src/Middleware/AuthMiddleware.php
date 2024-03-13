<?php

declare(strict_types=1);

namespace Spiral\Auth\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\ScopeInterface;

/**
 * Manages auth context scope.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'authContext';
    public const TOKEN_STORAGE_ATTRIBUTE = 'tokenStorage';

    /**
     * @param ScopeInterface $scope Deprecated, will be removed in v4.0.
     */
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly ActorProviderInterface $actorProvider,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TransportRegistry $transportRegistry,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $authContext = $this->initContext($request, new AuthContext($this->actorProvider, $this->eventDispatcher));

        $response = $handler->handle(
            $request
                ->withAttribute(self::ATTRIBUTE, $authContext)
                ->withAttribute(self::TOKEN_STORAGE_ATTRIBUTE, $this->tokenStorage),
        );

        return $this->closeContext($request, $response, $authContext);
    }

    private function initContext(Request $request, AuthContextInterface $authContext): AuthContextInterface
    {
        foreach ($this->transportRegistry->getTransports() as $name => $transport) {
            $tokenID = $transport->fetchToken($request);
            if ($tokenID === null) {
                continue;
            }

            $token = $this->tokenStorage->load($tokenID);
            if ($token === null) {
                continue;
            }

            // found valid token
            $authContext->start($token, $name);
            return $authContext;
        }

        return $authContext;
    }

    private function closeContext(Request $request, Response $response, AuthContextInterface $authContext): Response
    {
        if ($authContext->getToken() === null) {
            return $response;
        }

        $transport = $this->transportRegistry->getTransport($authContext->getTransport());

        if ($authContext->isClosed()) {
            $this->tokenStorage->delete($authContext->getToken());

            return $transport->removeToken(
                $request,
                $response,
                $authContext->getToken()->getID(),
            );
        }

        return $transport->commitToken(
            $request,
            $response,
            $authContext->getToken()->getID(),
            $authContext->getToken()->getExpiresAt(),
        );
    }
}
