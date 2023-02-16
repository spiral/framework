<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Traits\TargetTrait;

/**
 * When applied to a listener, this attribute will instruct the tokenizer to listen for classes that are extending or
 * implementing the given class or have the given trait.
 * @see TokenizationListenerInterface
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class TargetClass extends AbstractTarget
{
    use TargetTrait;

    public function filter(array $classes): \Generator
    {
        $target = new \ReflectionClass($this->class);

        foreach ($classes as $class) {
            if (!$target->isTrait()) {
                if ($class->isSubclassOf($target) || $class->getName() === $target->getName()) {
                    yield $class->getName();
                }

                continue;
            }

            // Checking using traits
            if (\in_array($target->getName(), $this->fetchTraits($class->getName()), true)) {
                yield $class->getName();
            }
        }
    }
}
