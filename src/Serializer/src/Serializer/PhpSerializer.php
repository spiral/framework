<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\SerializerInterface;

final class PhpSerializer implements SerializerInterface
{
    public function serialize(mixed $payload): string|\Stringable
    {
        return \serialize($payload);
    }

    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        if (\is_object($type)) {
            $type = $type::class;
        }

        if (\is_string($type) && !\class_exists($type) && !\interface_exists($type)) {
            throw new InvalidArgumentException(\sprintf('Class or interface `%s` doesn\'t exist.', $type));
        }

        return $this->runUnserialize($payload, $type);
    }

    private function runUnserialize(\Stringable|string $payload, ?string $type = null): mixed
    {
        $result = \unserialize((string) $payload, $type ? [] : ['allowed_classes' => false]);

        if ($result === false) {
            throw new UnserializeException('Failed to unserialize data.');
        }

        if ($type !== null && !$result instanceof $type) {
            throw new InvalidArgumentException(\sprintf(
                'Data received after unserializing must be of type: `%s`, received `%s`',
                $type,
                \get_debug_type($result)
            ));
        }

        return $result;
    }
}
