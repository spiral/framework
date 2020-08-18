<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
     * @param string $id
     * @return TokenInterface|null
     *
     * @throws TokenStorageException
     */
    public function load(string $id): ?TokenInterface;

    /**
     * Create token based on the payload provided by actor provider.
     *
     * @param array                   $payload
     * @param \DateTimeInterface|null $expiresAt
     * @return TokenInterface
     *
     * @throws TokenStorageException
     */
    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface;

    /**
     * Delete token from the persistent storage.
     *
     * @param TokenInterface $token
     *
     * @throws TokenStorageException
     */
    public function delete(TokenInterface $token): void;
}
