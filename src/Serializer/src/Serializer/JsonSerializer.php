<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Exception\SerializeException;
use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\SerializerInterface;

final class JsonSerializer implements SerializerInterface
{
    /**
     * @throws SerializeException
     */
    public function serialize(mixed $payload): string|\Stringable
    {
        try {
            return \json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \JsonException
     */
    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        if ($type !== null) {
            throw new InvalidArgumentException(
                \sprintf('Serializer `%s` does not support data hydration to an object.', self::class)
            );
        }

        try {
            return \json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnserializeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
