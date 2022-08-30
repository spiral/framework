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
use Cycle\ORM\TransactionInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Throwable;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 * Provides the ability to fetch token information from the database via Cycle ORM.
 */
final class TokenStorage implements TokenStorageInterface
{
    /** @var ORMInterface */
    private $orm;

    /** @var TransactionInterface */
    private $em;

    /**
     * @param ORMInterface         $orm
     * @param TransactionInterface $transaction
     */
    public function __construct(ORMInterface $orm, TransactionInterface $transaction)
    {
        $this->orm = $orm;
        $this->em = $transaction;
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

        $expiresAt = $token->getExpiresAt();
        if ($expiresAt !== null && $expiresAt < new DateTimeImmutable()) {
            $this->delete($token);
            return null;
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function create(array $payload, DateTimeInterface $expiresAt = null): TokenInterface
    {
        try {
            $token = new Token(
                $this->issueID(),
                $this->randomHash(128),
                $payload,
                new DateTimeImmutable(),
                $expiresAt
            );

            $this->em->persist($token);
            $this->em->run();

            return $token;
        } catch (Throwable $e) {
            throw new TokenStorageException('Unable to create token', $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TokenInterface $token): void
    {
        try {
            $this->em->delete($token);
            $this->em->run();
        } catch (Throwable $e) {
            throw new TokenStorageException('Unable to delete token', $e->getCode(), $e);
        }
    }

    /**
     * Issue unique token id.
     *
     * @return string
     * @throws Throwable
     */
    private function issueID(): string
    {
        $id = $this->randomHash(64);

        $query = $this->orm->getSource(Token::class)
            ->getDatabase()
            ->select()
            ->from($this->orm->getSource(Token::class)->getTable());

        while ((clone $query)->where('id', $id)->count('id') !== 0) {
            $id = $this->randomHash(64);
        }

        return $id;
    }

    /**
     * @param int $length
     * @return string
     *
     * @throws Throwable
     */
    private function randomHash(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
