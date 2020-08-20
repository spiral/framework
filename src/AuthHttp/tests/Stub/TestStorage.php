<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;

class TestStorage implements TokenStorageInterface
{
    public function load(string $id): ?TokenInterface
    {
        if ($id === 'bad') {
            return null;
        }

        return new TestToken($id, ['id' => $id]);
    }

    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        return new TestToken(
            $payload['id'],
            $payload,
            $expiresAt
        );
    }

    public function delete(TokenInterface $token): void
    {
    }
}
