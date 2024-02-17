<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Exception\ScopeException;

/**
 * Provides global access to temporary authentication scope.
 * @deprecated Use {@see AuthContextInterface} instead. Will be removed in v4.0.
 */
final class AuthScope implements AuthContextInterface
{
    public function __construct(
        #[Proxy] private readonly ContainerInterface $container
    ) {
    }

    /**
     * @throws ScopeException
     */
    public function start(TokenInterface $token, string $transport = null): void
    {
        $this->getAuthContext()->start($token, $transport);
    }

    /**
     * @throws ScopeException
     */
    public function getToken(): ?TokenInterface
    {
        return $this->getAuthContext()->getToken();
    }

    /**
     * @throws ScopeException
     */
    public function getTransport(): ?string
    {
        return $this->getAuthContext()->getTransport();
    }

    /**
     * @throws ScopeException
     */
    public function getActor(): ?object
    {
        return $this->getAuthContext()->getActor();
    }

    /**
     * @throws ScopeException
     */
    public function close(): void
    {
        $this->getAuthContext()->close();
    }

    /**
     * @throws ScopeException
     */
    public function isClosed(): bool
    {
        return $this->getAuthContext()->isClosed();
    }

    private function getAuthContext(): AuthContextInterface
    {
        try {
            return $this->container->get(AuthContextInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to resolve auth context, invalid scope', $e->getCode(), $e);
        }
    }
}
