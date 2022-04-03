<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

interface AttributedInterface
{
    public function setAttribute(string $name, mixed $value): void;

    /**
     * @param mixed $default If attribute is not set or equal to null.
     */
    public function getAttribute(string $name, mixed $default = null): mixed;

    public function getAttributes(): array;
}
