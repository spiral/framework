<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Input;

use Spiral\DataGrid\InputInterface;

final class NullInput implements InputInterface
{
    public function withNamespace(string $namespace): InputInterface
    {
        return clone $this;
    }

    public function hasValue(string $option): bool
    {
        return false;
    }

    public function getValue(string $option, mixed $default = null): mixed
    {
        return $default;
    }
}
