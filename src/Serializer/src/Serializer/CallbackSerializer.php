<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\SerializerInterface;

final class CallbackSerializer implements SerializerInterface
{
    /** @var callable */
    private mixed $serializeCallback;

    /** @var callable */
    private mixed $unserializeCallback;

    public function __construct(callable $serializeCallback, callable $unserializeCallback)
    {
        $this->serializeCallback = $serializeCallback;
        $this->unserializeCallback = $unserializeCallback;
    }

    public function serialize(mixed $payload): string
    {
        return ($this->serializeCallback)($payload);
    }

    public function unserialize(string|\Stringable $payload, string|object|null $type = null): mixed
    {
        return ($this->unserializeCallback)($payload, $type);
    }
}
