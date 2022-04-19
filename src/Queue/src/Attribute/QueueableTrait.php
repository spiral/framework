<?php

declare(strict_types=1);

namespace Spiral\Queue\Attribute;

use Spiral\Attributes\Factory;
use Spiral\Queue\QueueableInterface;

trait QueueableTrait
{
    /**
     * @psalm-param class-string|object $class
     */
    public function findQueueable($object): ?Queueable
    {
        $reflection = new \ReflectionClass($object);

        if ($reflection->isInterface()) {
            return null;
        }

        if ($reflection->implementsInterface(QueueableInterface::class)) {
            return $this->createQueueable($reflection, $object);
        }

        return (new Factory())->create()->firstClassMetadata($reflection, Queueable::class);
    }

    /**
     * @psalm-param class-string|object $object
     */
    private function createQueueable(\ReflectionClass $reflection, $object): Queueable
    {
        $queueable = new Queueable();

        if (\is_object($object) && $reflection->hasMethod('getQueue')) {
            $queueable->queue = $reflection->getMethod('getQueue')->invoke($object);
        }

        return $queueable;
    }
}
