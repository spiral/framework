<?php

declare(strict_types=1);

namespace Spiral\Serializer;

use Spiral\Serializer\Exception\SerializerNotFoundException;

interface SerializerRegistryInterface
{
    public function register(string $name, SerializerInterface $serializer): void;

    /**
     * @throws SerializerNotFoundException
     */
    public function get(string $name): SerializerInterface;

    public function has(string $name): bool;
}
