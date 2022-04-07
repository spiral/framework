<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Throwable;
use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;
use Spiral\Queue\Exception\SerializationException;

/**
 * @internal
 */
final class DefaultSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function serialize(array $payload): string
    {
        try {
            return serialize($payload);
        } catch (Throwable $e) {
            throw new SerializationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(string $payload): array
    {
        try {
            return (array)unserialize($payload);
        } catch (Throwable $e) {
            throw new SerializationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
