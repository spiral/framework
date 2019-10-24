<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var SessionScope */
    private $session;

    /**
     * @param SessionScope $session
     */
    public function __construct(SessionScope $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function load(string $id): ?TokenInterface
    {
        try {
            $tokenData = $this->session->getSection(self::SESSION_SECTION)->get('token');
            $token = Token::unpack($tokenData);
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to load session token', $e->getCode(), $e);
        }

        if (!hash_equals($token->getID(), $id)) {
            return null;
        }

        if ($token->getExpiresAt() !== null && $token->getExpiresAt() > new \DateTime()) {
            $this->delete($token);
            return null;
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        try {
            $token = new Token($this->randomHash(128), $payload, $expiresAt);
            $this->session->getSection(self::SESSION_SECTION)->set('token', $token->pack());

            return $token;
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to create session token', $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TokenInterface $token): void
    {
        $this->session->getSection(self::SESSION_SECTION)->delete('token');
    }

    /**
     * @param int $length
     * @return string
     *
     * @throws \Exception
     */
    private function randomHash(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
