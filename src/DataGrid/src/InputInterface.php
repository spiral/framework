<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

interface InputInterface
{
    /**
     * Isolate the input into given namespace (prefix).
     */
    public function withNamespace(string $namespace): InputInterface;

    public function hasValue(string $option): bool;

    public function getValue(string $option, mixed $default = null): mixed;
}
