<?php

declare(strict_types=1);

namespace Spiral\Queue;

final class SerializerRegistry implements SerializerInterface, SerializerRegistryInterface
{
    /** @var array<non-empty-string, SerializerInterface> */
    private array $serializers = [];
    private SerializerInterface $default;

    /** @param array<non-empty-string, SerializerInterface> $serializers */
    public function __construct(SerializerInterface $default)
    {
        $this->default = $default;
    }

    public function serialize(array $payload): string
    {
        return $this->default->serialize($payload);
    }

    public function deserialize(string $payload): array
    {
        return $this->default->deserialize($payload);
    }

    public function getSerializer(string $jobType): SerializerInterface
    {
        if (!$this->hasSerializer($jobType)) {
            return $this->default;
        }

        return $this->serializers[$jobType];
    }

    public function addSerializer(string $jobType, SerializerInterface $serializer): void
    {
        if (!$this->hasSerializer($jobType)) {
            $this->serializers[$jobType] = $serializer;
        }
    }

    public function hasSerializer(string $jobType): bool
    {
        return isset($this->serializers[$jobType]);
    }
}
