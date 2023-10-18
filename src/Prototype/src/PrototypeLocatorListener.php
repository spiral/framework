<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Prototype\Annotation\Prototyped;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Traits\TargetTrait;

#[TargetAttribute(Prototyped::class)]
final class PrototypeLocatorListener implements TokenizationListenerInterface
{
    use TargetTrait;

    /** @var array<non-empty-string, class-string> */
    private array $attributes = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly PrototypeBootloader $prototype
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        $attribute = $this->reader->firstClassMetadata($class, Prototyped::class);
        if ($attribute === null) {
            return;
        }

        $this->attributes[$attribute->property] = $class->getName();
    }

    public function finalize(): void
    {
        foreach ($this->attributes as $property => $class) {
            $this->prototype->bindProperty($property, $class);
        }
    }
}
