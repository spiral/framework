<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

use Spiral\DataGrid\Specification\ValueInterface;

/**
 * Note that the nested values/accessors are executed after the parent one.
 */
abstract class Accessor implements ValueInterface
{
    public function __construct(
        protected ValueInterface $next
    ) {
    }

    final public function accepts(mixed $value): bool
    {
        return $this->acceptsCurrent($value) || $this->next->accepts($value);
    }

    final public function convert(mixed $value): mixed
    {
        return $this->next->convert($this->convertCurrent($value));
    }

    abstract protected function acceptsCurrent(mixed $value): bool;

    abstract protected function convertCurrent(mixed $value): mixed;
}
