<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Auth;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Exception\ScopeException;

/**
 * Provides global access to temporary authentication scope.
 */
final class AuthScope implements AuthContextInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function start(TokenInterface $token, string $transport = null): void
    {
        $this->getAuthContext()->start($token, $transport);
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getToken(): ?TokenInterface
    {
        return $this->getAuthContext()->getToken();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getTransport(): ?string
    {
        return $this->getAuthContext()->getTransport();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getActor(): ?object
    {
        return $this->getAuthContext()->getActor();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function close(): void
    {
        $this->getAuthContext()->close();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function isClosed(): bool
    {
        return $this->getAuthContext()->isClosed();
    }

    /**
     * @return AuthContextInterface
     */
    private function getAuthContext(): AuthContextInterface
    {
        try {
            return $this->container->get(AuthContextInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to resolve auth context, invalid scope', $e->getCode(), $e);
        }
    }
}