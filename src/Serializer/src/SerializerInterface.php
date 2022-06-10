<?php

declare(strict_types=1);

namespace Spiral\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $payload): string|\Stringable;

    public function unserialize(string|\Stringable $payload, string|object|null $type = null): mixed;
}
