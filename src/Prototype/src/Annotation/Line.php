<?php

declare(strict_types=1);

namespace Spiral\Prototype\Annotation;

/**
 * Singular annotation line.
 *
 * @deprecated since v3.9.0
 */
final class Line
{
    public function __construct(
        public string $value,
        public ?string $type = null
    ) {
    }

    public function is(array $type): bool
    {
        if ($this->type === null) {
            return false;
        }

        return \in_array(\strtolower($this->type), $type, true);
    }

    public function isStructured(): bool
    {
        return $this->type !== null;
    }

    public function isEmpty(): bool
    {
        return !$this->isStructured() && \trim($this->value) === '';
    }
}
