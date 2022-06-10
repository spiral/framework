<?php

declare(strict_types=1);

namespace Spiral\Serializer;

use Spiral\Serializer\Exception\SerializerNotFoundException;

class SerializerCollection implements \IteratorAggregate, \Countable
{
    private array $serializers = [];

    public function __construct(array $serializers = [])
    {
        foreach ($serializers as $name => $serializer) {
            $this->add($name, $serializer);
        }
    }

    /**
     * @return \ArrayIterator<string, SerializerInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    public function count(): int
    {
        return \count($this->serializers);
    }

    public function add(string $name, SerializerInterface $serializer)
    {
        $this->serializers[$name] = $serializer;
    }

    /**
     * @return array<string, SerializerInterface>
     */
    public function all(): array
    {
        return $this->serializers;
    }

    public function get(string $name): SerializerInterface
    {
        return $this->serializers[$name] ?? throw new SerializerNotFoundException($name);
    }

    public function remove(string|array $name)
    {
        foreach ((array) $name as $n) {
            unset($this->serializers[$n]);
        }
    }
}
