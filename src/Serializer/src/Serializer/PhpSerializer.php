<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\SerializerInterface;

final class PhpSerializer implements SerializerInterface
{
    public function serialize(mixed $payload): string|\Stringable
    {
        return serialize($payload);
    }

    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        if ($type === null) {
            return unserialize((string) $payload, ['allowed_classes' => false]);
        }

        if (\is_object($type)) {
            $type = $type::class;
        }

        $result = unserialize((string) $payload, ['allowed_classes' => [$type]]);

        if (!$result instanceof $type) {
            throw new UnserializeException(\sprintf(
                'Data received after unserializing must be of type: %s, received %s.',
                $type,
                \get_debug_type($result)
            ));
        }

        return $result;
    }
}
