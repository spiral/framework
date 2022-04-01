<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Fixture;

use Spiral\DataGrid\Specification\Value\Accessor\Accessor;
use Spiral\DataGrid\Specification\ValueInterface;

class Add extends Accessor
{
    /** @var int */
    private $val;

    public function __construct(ValueInterface $next, int $val)
    {
        parent::__construct($next);
        $this->val = $val;
    }

    protected function acceptsCurrent($value): bool
    {
        return is_numeric($value);
    }

    protected function convertCurrent($value): mixed
    {
        return $value + $this->val;
    }
}
