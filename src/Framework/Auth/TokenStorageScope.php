<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ScopeException;

/**
 * @deprecated Use {@see TokenStorageInterface} instead. Will be removed in v4.0.
 */
#[Singleton]
final class TokenStorageScope implements TokenStorageInterface
{
    public function __construct(
        #[Proxy] private readonly ContainerInterface $container
    ) {
    }

    /**
     * Load token by id, must return null if token not found.
     *
     * @throws TokenStorageException
     */
    public function load(string $id): ?TokenInterface
    {
        return $this->getTokenStorage()->load($id);
    }

    /**
     * Create token based on the payload provided by actor provider.
     *
     * @throws TokenStorageException
     */
    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        return $this->getTokenStorage()->create($payload, $expiresAt);
    }

    /**
     * Delete token from the persistent storage.
     *
     * @throws TokenStorageException
     */
    public function delete(TokenInterface $token): void
    {
        $this->getTokenStorage()->delete($token);
    }

    /**
     * @throws ScopeException
     */
    private function getTokenStorage(): TokenStorageInterface
    {
        try {
            return $this->container->get(TokenStorageInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to resolve token storage, invalid scope', $e->getCode(), $e);
        }
    }
}
