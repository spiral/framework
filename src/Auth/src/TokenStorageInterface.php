<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Auth\Exception\TokenStorageException;

/**
 * Provides the ability to store tokens in persistent storage.
 */
interface TokenStorageInterface
{
    /**
     * Load token by id, must return null if token not found.
     *
     * @throws TokenStorageException
     */
    public function load(string $id): ?TokenInterface;

    /**
     * Create token based on the payload provided by actor provider.
     *
     * @param \DateTimeInterface|null $expiresAt
     *
     * @throws TokenStorageException
     */
    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface;

    /**
     * Delete token from the persistent storage.
     *
     * @throws TokenStorageException
     */
    public function delete(TokenInterface $token): void;
}
