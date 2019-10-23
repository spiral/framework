<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Auth\Session;

use Spiral\Auth\TokenInterface;

final class Token implements TokenInterface, \JsonSerializable
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

    /**
     * Pack token data into array form.
     *
     * @return array
     */
    public function pack(): array
    {
        return [
            'id'        => $this->id,
            'expiresAt' => $this->expiresAt,
            'payload'   => $this->payload
        ];
    }

    /**
     * Unpack token from serialized data.
     *
     * @param array $data
     * @return Token
     * @throws \Exception
     */
    public static function unpack(array $data): Token
    {
        $expiresAt = null;
        if ($data['expiredAt'] != null) {
            $expiresAt = (new \DateTimeImmutable())->setTimestamp($data['expiredAt']);
        }

        return new Token($data['id'], $data['payload'], $expiresAt);
    }
}