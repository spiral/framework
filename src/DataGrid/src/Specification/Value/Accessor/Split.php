<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

use Spiral\DataGrid\Specification\ValueInterface;

class Split extends Accessor
{
    public function __construct(
        ValueInterface $next,
        private string $char = ','
    ) {
        parent::__construct($next);
    }

    protected function acceptsCurrent(mixed $value): bool
    {
        return \is_string($value);
    }

    protected function convertCurrent(mixed $value): array
    {
        return \explode($this->char, (string) $value);
    }
}
