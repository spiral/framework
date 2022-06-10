<?php

declare(strict_types=1);

namespace Spiral\Serializer;

class SerializerManager implements SerializerInterface
{
    public function __construct(
        protected readonly SerializerCollection $serializers
    ) {
    }

    public function getSerializer(string $format = null): SerializerInterface
    {
        return $this->serializers->get($format);
    }

    public function serialize(mixed $payload, ?string $format = null): string|\Stringable
    {
        return $this->getSerializer($format)->serialize($payload, $format);
    }

    public function unserialize(
        string|\Stringable $payload,
        string|object|null $type = null,
        ?string $format = null
    ): mixed {
        return $this->getSerializer($format)->unserialize($payload, $type, $format);
    }
}
