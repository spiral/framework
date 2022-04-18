<?php

declare(strict_types=1);

namespace Spiral\Queue\Attribute;

use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;

trait QueueableTrait
{
    private ReaderInterface $reader;

    public function setReader(ReaderInterface $reader): self
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @psalm-param class-string $class
     */
    public function findQueueable(string $class): ?Queueable
    {
        $this->reader ??= (new Factory())->create();

        $reflection = new \ReflectionClass($class);

        if ($reflection->isInterface()) {
            return null;
        }

        return $this->reader->firstClassMetadata($reflection, Queueable::class);
    }
}
