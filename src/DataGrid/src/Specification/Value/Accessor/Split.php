<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

use Spiral\DataGrid\Specification\ValueInterface;

class Split extends Accessor
{
    /** @var string */
    private $char;

    public function __construct(ValueInterface $next, string $char = ',')
    {
        parent::__construct($next);
        $this->char = $char;
    }

    protected function acceptsCurrent($value): bool
    {
        return is_string($value);
    }

    protected function convertCurrent($value)
    {
        return explode($this->char, $value);
    }
}
