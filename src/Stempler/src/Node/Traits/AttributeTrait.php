<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\Traits;

trait AttributeTrait
{
    private array $attributes = [];

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
