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
use DateTimeInterface;
use Spiral\Auth\TokenInterface;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 * @Cycle\Entity(table="auth_tokens")
 */
final class Token implements TokenInterface
{
    /** @Cycle\Column(type="string(64)", primary=true) */
    private $id;

    /** @var string */
    private $secretValue;

    /** @Cycle\Column(type="string(128)", name="hashed_value") */
    private $hashedValue;

    /** @Cycle\Column(type="datetime") */
    private $createdAt;

    /** @Cycle\Column(type="datetime", nullable=true) */
    private $expiresAt;

    /** @Cycle\Column(type="blob") */
    private $payload;

    /**
     * @param string                 $id
     * @param string                 $secretValue
     * @param array                  $payload
     * @param DateTimeInterface      $createdAt
     * @param DateTimeInterface|null $expiresAt
     */
    public function __construct(
        string $id,
        string $secretValue,
        array $payload,
        DateTimeInterface $createdAt,
        DateTimeInterface $expiresAt = null
    ) {
        $this->id = $id;

        $this->secretValue = $secretValue;
        $this->hashedValue = hash('sha512', $secretValue);

        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;

        $this->payload = json_encode($payload);
    }

    /**
     * @param string $value
     */
    public function setSecretValue(string $value): void
    {
        $this->secretValue = $value;
    }

    /**
     * @inheritDoc
     */
    public function getID(): string
    {
        return sprintf('%s:%s', $this->id, $this->secretValue);
    }

    /**
     * @return string
     */
    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        if (is_resource($this->payload)) {
            // postgres
            return json_decode(stream_get_contents($this->payload), true);
        }

        return json_decode($this->payload, true);
    }
}
