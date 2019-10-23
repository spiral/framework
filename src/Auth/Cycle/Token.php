<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Auth\Cycle;

use Cycle\Annotated\Annotation as Cycle;
use Spiral\Auth\TokenInterface;

/**
 * @Cycle\Entity(table="auth_tokens", repository="TokenRepository")
 * @Cycle\Table(indexes={
 *     @Cycle\Index(columns={"id", "hash"}, unique=true)
 * })
 */
final class Token implements TokenInterface, \JsonSerializable
{
    /** @Cycle\Column(type="bigPrimary") */
    private $id;

    /** @Cycle\Column(type="varchar(128)") */
    private $hash;

    /** @Cycle\Column(type="datetime") */
    private $createdAt;

    /** @Cycle\Column(type="datetime", nullable=true) */
    private $expiresAt;

    /** @Cycle\Column(type="blob") */
    private $payload;

    /**
     * @param string                  $hash
     * @param array                   $payload
     * @param \DateTimeImmutable      $createdAt
     * @param \DateTimeInterface|null $expiresAt
     */
    public function __construct(
        string $hash,
        array $payload,
        \DateTimeImmutable $createdAt,
        \DateTimeInterface $expiresAt = null
    ) {
        $this->hash = $hash;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->payload = json_encode($payload);
    }

    /**
     * @inheritDoc
     */
    public function getID(): string
    {
        return sprintf("%s:%s", $this->id, $this->hash);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }


    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return json_decode($this->payload, true);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getID();
    }
}