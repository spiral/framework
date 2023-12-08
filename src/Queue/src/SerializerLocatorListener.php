<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Attributes\ReaderInterface;
use Spiral\Queue\Attribute\JobHandler as JobHandlerAttribute;
use Spiral\Queue\Attribute\Serializer;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(Serializer::class)]
final class SerializerLocatorListener implements TokenizationListenerInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly QueueRegistry $registry,
        private readonly QueueConfig $config,
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        $attribute = $this->reader->firstClassMetadata($class, Serializer::class);
        if ($attribute === null) {
            return;
        }

        $this->registry->setSerializer($this->getJobType($class), $attribute->serializer);
    }

    public function finalize(): void
    {
    }

    private function getJobType(\ReflectionClass $class): string
    {
        $attribute = $this->reader->firstClassMetadata($class, JobHandlerAttribute::class);
        if ($attribute !== null) {
            return $attribute->type;
        }

        foreach ($this->config->getRegistryHandlers() as $jobType => $handler) {
            if (\is_object($handler)) {
                $handler = $handler::class;
            }

            if ($handler === $class->getName()) {
                return $jobType;
            }
        }

        return $class->getName();
    }
}
