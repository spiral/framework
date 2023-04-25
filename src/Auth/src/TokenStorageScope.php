<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Core\Container\SingletonInterface;

final class TokenStorageScope implements TokenStorageInterface, SingletonInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Load token by id, must return null if token not found.
     *
     * @throws TokenStorageException
     */
    public function load(string $id): ?TokenInterface
    {
        return $this->tokenStorage->load($id);
    }

    /**
     * Create token based on the payload provided by actor provider.
     *
     * @throws TokenStorageException
     */
    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        return $this->tokenStorage->create($payload, $expiresAt);
    }

    /**
     * Delete token from the persistent storage.
     *
     * @throws TokenStorageException
     */
    public function delete(TokenInterface $token): void
    {
        $this->tokenStorage->delete($token);
    }
}
