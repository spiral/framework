<?php

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Context\ValueDependency;

/**
 * ContextGenerator creates all possible variations of context values. Use this class
 * to properly warm up views cache.
 */
final class ContextGenerator
{
    public function __construct(
        private readonly ContextInterface $context
    ) {
    }

    /**
     * Generate all possible context variations.
     *
     * @return ContextInterface[]
     */
    public function generate(): array
    {
        $dependencies = $this->context->getDependencies();

        return $this->rotate(new ViewContext(), $dependencies);
    }

    /**
     * Rotate all possible context values using recursive tree walk.
     *
     * @param DependencyInterface[] $dependencies
     *
     * @return ContextInterface[]
     *
     * @psalm-return list<ContextInterface>
     */
    private function rotate(ContextInterface $context, array $dependencies): array
    {
        if (empty($dependencies)) {
            return [];
        }

        $top = \array_shift($dependencies);

        $variants = [];
        foreach ($top->getVariants() as $value) {
            $variant = $context->withDependency(new ValueDependency($top->getName(), $value));

            if (empty($dependencies)) {
                $variants[] = $variant;
                continue;
            }

            foreach ($this->rotate($variant, $dependencies) as $inner) {
                $variants[] = $inner;
            }
        }

        return $variants;
    }
}
