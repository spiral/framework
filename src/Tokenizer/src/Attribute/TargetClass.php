<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Traits\TargetTrait;

/**
 * When applied to a listener, this attribute will instruct the tokenizer to listen for classes that are extending or
 * implementing the given class.
 * @see TokenizationListenerInterface
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class TargetClass implements ListenerDefinitionInterface
{
    use TargetTrait;

    /**
     * @param class-string $class
     * @param non-empty-string|null $scope
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $scope = null,
    ) {
    }

    public function filter(array $classes): \Generator
    {
        $target = new \ReflectionClass($this->class);

        foreach ($classes as $class) {
            if (!$target->isTrait()) {
                if ($class->isSubclassOf($target) || $class->getName() === $target->getName()) {
                    yield $class->getName();
                    continue;
                }
            }

            // Checking using traits
            if (\in_array($target->getName(), $this->fetchTraits($class->getName()))) {
                yield $class->getName();
            }
        }
    }

    public function getCacheKey(): string
    {
        return \md5($this->class . $this->scope);
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
