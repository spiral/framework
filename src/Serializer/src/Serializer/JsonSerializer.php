<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\SerializerInterface;

final class JsonSerializer implements SerializerInterface
{
    /**
     * @throws \JsonException
     */
    public function serialize(mixed $payload): string|\Stringable
    {
        return \json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        return \json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR);
    }
}
