<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

/**
 * Provides a basic implementation of the {@see AttributedInterface}.
 */
trait AttributedTrait
{
    /** @var array<non-empty-string, mixed> */
    private array $attributes = [];

    /**
     * @return array<non-empty-string, mixed> Attributes derived from the context.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param non-empty-string $name
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @param non-empty-string $name
     */
    public function withAttribute(string $name, mixed $value): static
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * @param non-empty-string $name
     */
    public function withoutAttribute(string $name): static
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
