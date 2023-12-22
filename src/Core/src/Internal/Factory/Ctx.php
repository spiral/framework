<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Factory;

/**
 * @template TClass of object
 * @internal
 */
final class Ctx
{
    /**
     * @param class-string<TClass> $class
     * @param null|\ReflectionClass<TClass> $reflection
     */
    public function __construct(
        public readonly string $alias,
        public string $class,
        public \Stringable|string|null $context = null,
        public ?bool $singleton = null,
        public ?\ReflectionClass $reflection = null,
    ) {
    }
}
