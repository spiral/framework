<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\SerializerInterface;

final class PhpSerializer implements SerializerInterface
{
    public function serialize(mixed $payload): string|\Stringable
    {
        return serialize($payload);
    }

    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        return unserialize((string) $payload);
    }
}
