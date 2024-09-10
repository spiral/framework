<?php

declare(strict_types=1);

namespace Spiral\Views\Context;

use Spiral\Views\DependencyInterface;

/**
 * Fixed value dependency.
 */
final class ValueDependency implements DependencyInterface
{
    private readonly array $variants;

    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
        array $variants = null
    ) {
        $this->variants = $variants ?? [$value];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getVariants(): array
    {
        return $this->variants;
    }
}
