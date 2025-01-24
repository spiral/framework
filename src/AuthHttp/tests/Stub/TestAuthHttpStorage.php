<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;

class TestAuthHttpStorage implements TokenStorageInterface
{
    public function load(string $id): ?TokenInterface
    {
        if ($id === 'bad') {
            return null;
        }

        return new TestAuthHttpToken($id, ['id' => $id]);
    }

    public function create(array $payload, ?\DateTimeInterface $expiresAt = null): TokenInterface
    {
        return new TestAuthHttpToken(
            $payload['id'],
            $payload,
            $expiresAt,
        );
    }

    public function delete(TokenInterface $token): void {}
}
