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

        [$pk, $hash] = explode(':', $id, 2);

        /** @var Token $token */
        $token = $this->orm->getRepository(Token::class)->findByPK($pk);

        if ($token === null || !hash_equals($token->getHashedValue(), hash('sha512', $hash))) {
            // hijacked or deleted
            return null;
        }

        $token->setSecretValue($hash);

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
            $token = new Token(
                $this->issueID(),
                $this->randomHash(128),
                $payload,
                new \DateTimeImmutable(),
                $expiresAt
            );

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
     * Issue unique token id.
     *
     * @return string
     * @throws \Exception
     */
    private function issueID(): string
    {
        $id = $this->randomHash(64);

        $query = $this->orm->getSource(Token::class)->getDatabase()->select()->from(
            $this->orm->getSource(Token::class)->getTable()
        );

        while ((clone $query)->where('id', $id)->count('id') !== 0) {
            $id = $this->randomHash(64);
        }

        return $id;
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
