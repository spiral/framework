<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Attributes\ReaderInterface;
use Spiral\Queue\Attribute\Queueable;

class QueueableDetector
{
    private ReaderInterface $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @psalm-param class-string|object $object
     */
    public function isQueueable($object): bool
    {
        $reflection = new \ReflectionClass($object);

        if ($reflection->implementsInterface(QueueableInterface::class)) {
            return true;
        }

        return $this->reader->firstClassMetadata($reflection, Queueable::class) !== null;
    }

    /**
     * @psalm-param class-string|object $object
     */
    public function getQueue($object): ?string
    {
        $reflection = new \ReflectionClass($object);

        $attribute = $this->reader->firstClassMetadata($reflection, Queueable::class);
        if ($attribute !== null) {
            return $attribute->queue;
        }

        if (\is_object($object) && $reflection->hasMethod('getQueue')) {
            return $reflection->getMethod('getQueue')->invoke($object);
        }

        return null;
    }
}
