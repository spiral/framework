<?php

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

/**
 * No styles.
 *
 * @deprecated since v2.13. Will be removed in v3.0
 */
final class PlainRenderer extends AbstractRenderer
{
    public function __construct(
        private readonly bool $escapeStrings = true
    ) {
    }

    public function apply(mixed $element, string $type, string $context = ''): string
    {
        return (string)$element;
    }

    public function escapeStrings(): bool
    {
        return $this->escapeStrings;
    }
}