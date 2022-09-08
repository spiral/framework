<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\TokenInterface;

class TestAuthHttpToken implements TokenInterface
{
    /** @var string */
    private $id;

    /** @var \DateTimeInterface|null */
    private $expiresAt;

    /** @var array */
    private $payload;

    /**
     * @param string                  $id
     * @param array                   $payload
     * @param \DateTimeInterface|null $expiresAt
     */
    public function __construct(string $id, array $payload, \DateTimeInterface $expiresAt = null)
    {
        $this->id = $id;
        $this->expiresAt = $expiresAt;
        $this->payload = $payload;
    }

    /**
     * @inheritDoc
     */
    public function getID(): string
    {
        return $this->id;
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
        return $this->payload;
    }
}
