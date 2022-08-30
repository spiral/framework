<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface SerializerInterface
{
    /**
     * Serializes payload.
     */
    public function serialize(array $payload): string;

    /**
     * Deserializes payload.
     */
    public function deserialize(string $payload): array;
}
