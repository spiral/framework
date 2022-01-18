<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface SerializerInterface
{
    /**
     * Serializes payload.
     *
     * @param array $payload
     * @return string
     */
    public function serialize(array $payload): string;

    /**
     * Deserializes payload.
     *
     * @param string $payload
     * @return array
     */
    public function deserialize(string $payload): array;
}
