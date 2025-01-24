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

    public function __construct(string $id, array $payload, ?\DateTimeInterface $expiresAt = null)
    {
        $this->id = $id;
        $this->expiresAt = $expiresAt;
        $this->payload = $payload;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
