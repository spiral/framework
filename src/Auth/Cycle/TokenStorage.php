<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;
use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;

/**
 * Provides the ability to fetch token information from the database via Cycle ORM.
 */
final class TokenStorage implements TokenStorageInterface
{
    /** @var ORMInterface */
    private $orm;

    /**
     * @param ORMInterface $orm
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;
    }

    /**
     * @inheritDoc
     */
    public function load(string $id): ?TokenInterface
    {
        if (strpos($id, ':') === false) {
            return null;
        }

        list($pk, $hash) = explode(':', $id, 2);

        if (!is_numeric($pk)) {
            return null;
        }

        /** @var TokenInterface $token */
        $token = $this->orm->getRepository(Token::class)->findByPK((int)$pk);

        if ($token === null || $token->getID() !== $id) {
            // hijacked or deleted
            return null;
        }

        if ($token->getExpiresAt() !== null && $token->getExpiresAt() < new \DateTime()) {
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
            $token = new Token($this->randomHash(128), $payload, new \DateTimeImmutable(), $expiresAt);

            (new Transaction($this->orm))->persist($token)->run();

            return $token;
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to create token', $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TokenInterface $token): void
    {
        try {
            (new Transaction($this->orm))->delete($token)->run();
        } catch (\Throwable $e) {
            throw new TokenStorageException('Unable to delete token', $e->getCode(), $e);
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
