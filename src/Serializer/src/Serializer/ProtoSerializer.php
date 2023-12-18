<?php

declare(strict_types=1);

namespace Spiral\Serializer\Serializer;

use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Exception\SerializerException;
use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\SerializerInterface;
use Google\Protobuf\Internal\Message;

final class ProtoSerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!\class_exists(Message::class)) {
            throw new SerializerException('Package `google/protobuf` is not installed.');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function serialize(mixed $payload): string|\Stringable
    {
        if (!$payload instanceof Message) {
            throw new InvalidArgumentException(\sprintf(
                'Payload must be of type `%s`, received `%s`.',
                Message::class,
                \get_debug_type($payload)
            ));
        }

        return $payload->serializeToString();
    }

    /**
     * @throws UnserializeException
     * @throws InvalidArgumentException
     */
    public function unserialize(\Stringable|string $payload, object|string|null $type = null): mixed
    {
        if (\is_object($type)) {
            $type = $type::class;
        }

        if ($type === null || !\class_exists($type) || !\is_a($type, Message::class, true)) {
            throw new InvalidArgumentException(\sprintf(
                'Parameter `$type` must be of type: `%s`, received `%s`.',
                Message::class,
                \get_debug_type($type)
            ));
        }

        $object = new $type();

        try {
            $object->mergeFromString((string) $payload);
        } catch (\Throwable $e) {
            throw new UnserializeException(
                \sprintf('Failed to unserialize data: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $object;
    }
}
