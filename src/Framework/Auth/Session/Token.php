<?php

declare(strict_types=1);

namespace Spiral\Auth\Session;

use Spiral\Auth\TokenInterface;

final class Token implements TokenInterface
{
    public function __construct(
        private readonly string $id,
        private readonly array $payload,
        private readonly ?\DateTimeInterface $expiresAt = null
    ) {
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

    /**
     * Pack token data into array form.
     */
    public function pack(): array
    {
        return [
            'id'        => $this->id,
            'expiresAt' => $this->expiresAt !== null ? $this->expiresAt->getTimestamp() : null,
            'payload'   => $this->payload,
        ];
    }

    /**
     * Unpack token from serialized data.
     *
     * @throws \Throwable
     */
    public static function unpack(array $data): Token
    {
        $expiresAt = null;
        if ($data['expiresAt'] !== null) {
            $expiresAt = (new \DateTimeImmutable())->setTimestamp($data['expiresAt']);
        }

        return new Token($data['id'], $data['payload'], $expiresAt);
    }
}
