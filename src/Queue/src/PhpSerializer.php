<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Queue\Exception\SerializationException;

final class PhpSerializer implements SerializerInterface
{
    public function serialize(array $payload): string
    {
        $result = \serialize($payload);

        if ($result === false) {
            throw new SerializationException('Failed to serialize data.');
        }

        return $result;
    }

    public function deserialize(string $payload): array
    {
        $result = \unserialize($payload);

        if ($result === false) {
            throw new SerializationException('Failed to unserialize data.');
        }

        return $result;
    }
}
