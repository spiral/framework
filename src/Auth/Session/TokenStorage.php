<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Auth\Session;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionSectionInterface;

/**
 * Store tokens in active session segment (received via scope).
 */
final class TokenStorage implements TokenStorageInterface
{
    // session section to store session information
    private const SESSION_SECTION = 'auth';

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
     */
    public function load(string $id): ?TokenInterface
    {
        try {
            $tokenData = $this->getAuthSection()->get('token');
            $token = Token::unpack($tokenData);
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to load session token', $e->getCode(), $e);
        }

        if ($token->getID() !== $id) {
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
            $token = new Token($this->randomHash(123), $payload, $expiresAt);
            $this->getAuthSection()->set('token', $token->pack());

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
        $this->getAuthSection()->delete('token');
    }

    /**
     * @return SessionSectionInterface
     */
    private function getAuthSection(): SessionSectionInterface
    {
        try {
            $session = $this->container->get(SessionInterface::class);

            /** @var SessionInterface $session */
            return $session->getSection(self::SESSION_SECTION);
        } catch (ContainerExceptionInterface $e) {
            throw new ScopeException('Unable to find auth token, invalid session scope', $e->getCode(), $e);
        }
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