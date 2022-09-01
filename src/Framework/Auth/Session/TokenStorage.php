<?php

declare(strict_types=1);

namespace Spiral\Auth\Session;

use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Session\SessionScope;

/**
 * Store tokens in active session segment (received via scope).
 */
final class TokenStorage implements TokenStorageInterface
{
    // session section to store session information
    private const SESSION_SECTION = 'auth';

    public function __construct(
        private readonly SessionScope $session
    ) {
    }

    public function load(string $id): ?TokenInterface
    {
        try {
            $tokenData = $this->session->getSection(self::SESSION_SECTION)->get('token');
            if ($tokenData === null) {
                return null;
            }

            $token = Token::unpack($tokenData);
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to load session token', (int) $e->getCode(), $e);
        }

        if (!\hash_equals($token->getID(), $id)) {
            return null;
        }

        $expiresAt = $token->getExpiresAt();
        if ($expiresAt !== null && $expiresAt < new \DateTimeImmutable()) {
            $this->delete($token);
            return null;
        }

        return $token;
    }

    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        try {
            $token = new Token($this->randomHash(128), $payload, $expiresAt);
            $this->session->getSection(self::SESSION_SECTION)->set('token', $token->pack());

            return $token;
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to create session token', (int) $e->getCode(), $e);
        }
    }

    public function delete(TokenInterface $token): void
    {
        $this->session->getSection(self::SESSION_SECTION)->delete('token');
    }

    /**
     * @param positive-int $length
     */
    private function randomHash(int $length): string
    {
        return \substr(\bin2hex(\random_bytes($length)), 0, $length);
    }
}
