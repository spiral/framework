<?php

declare(strict_types=1);

namespace Spiral\Serializer;

class SerializerManager implements SerializerInterface
{
    public function __construct(
        protected readonly SerializerRegistry $serializers,
        protected readonly string $defaultFormat
    ) {
    }

    public function getSerializer(string $format = null): SerializerInterface
    {
        return $this->serializers->get($format ?? $this->defaultFormat);
    }

    public function serialize(mixed $payload, ?string $format = null): string|\Stringable
    {
        return $this->getSerializer($format ?? $this->defaultFormat)->serialize($payload);
    }

    public function unserialize(
        string|\Stringable $payload,
        string|object|null $type = null,
        ?string $format = null
    ): mixed {
        return $this->getSerializer($format ?? $this->defaultFormat)->unserialize($payload, $type);
    }
}
