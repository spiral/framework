<?php

declare(strict_types=1);

namespace Spiral\Serializer;

use Spiral\Serializer\Exception\SerializerNotFoundException;

class SerializerRegistry implements SerializerRegistryInterface
{
    /** @var SerializerInterface[] */
    private array $serializers = [];

    public function __construct(array $serializers = [])
    {
        foreach ($serializers as $name => $serializer) {
            $this->register($name, $serializer);
        }
    }

    public function register(string $name, SerializerInterface $serializer): void
    {
        $this->serializers[$name] = $serializer;
    }

    /**
     * @throws SerializerNotFoundException
     */
    public function get(string $name): SerializerInterface
    {
        return
            $this->serializers[$name] ??
            throw new SerializerNotFoundException(\sprintf('Serializer with name [%s] not found.', $name));
    }

    public function has(string $name): bool
    {
        return isset($this->serializers[$name]);
    }

    public function hasByClass(string $class): bool
    {
        foreach ($this->serializers as $serializer) {
            if ($serializer::class === $class) {
                return true;
            }
        }

        return false;
    }

    public function getNameByClass(string $class): string
    {
        foreach ($this->serializers as $name => $serializer) {
            if ($serializer::class === $class) {
                return $name;
            }
        }

        throw new SerializerNotFoundException(\sprintf('Serializer [%s] not found.', $class));
    }
}
