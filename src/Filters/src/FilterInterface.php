<?php

declare(strict_types=1);

namespace Spiral\Filters;

interface FilterInterface
{
    public function isValid(): bool;

    public function getErrors(): array;

    /**
     * Associate the context with the filter.
     */
    public function setContext(mixed $context): void;

    /**
     * Return currently associated context.
     */
    public function getContext(): mixed;
}
