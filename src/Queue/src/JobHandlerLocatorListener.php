<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Attributes\ReaderInterface;
use Spiral\Queue\Attribute\JobHandler as Attribute;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(Attribute::class)]
final class JobHandlerLocatorListener implements TokenizationListenerInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly QueueRegistry $registry
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        $attribute = $this->reader->firstClassMetadata($class, Attribute::class);
        if ($attribute === null) {
            return;
        }

        $this->registry->setHandler($attribute->type, $class->getName());
    }

    public function finalize(): void
    {
    }
}
