<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
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

    /** @var ScopeInterface */
    private $scope;

    /** @var ActorProviderInterface */
    private $actorProvider;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TransportRegistry */
    private $transportRegistry;

    public function __construct(
        ScopeInterface $scope,
        ActorProviderInterface $actorProvider,
        TokenStorageInterface $tokenStorage,
        TransportRegistry $transportRegistry
    ) {
        $this->scope = $scope;
        $this->actorProvider = $actorProvider;
        $this->tokenStorage = $tokenStorage;
        $this->transportRegistry = $transportRegistry;
    }

    /**
     *
     * @throws \Throwable
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $authContext = $this->initContext($request, new AuthContext($this->actorProvider));

        $response = $this->scope->runScope(
            [AuthContextInterface::class => $authContext],
            static function () use ($request, $handler, $authContext) {
                return $handler->handle($request->withAttribute(self::ATTRIBUTE, $authContext));
            }
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
                $authContext->getToken()->getID()
            );
        }

        return $transport->commitToken(
            $request,
            $response,
            $authContext->getToken()->getID(),
            $authContext->getToken()->getExpiresAt()
        );
    }
}
