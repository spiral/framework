<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\Value\StringValue;

class Like extends Expression
{
    /** @var string */
    private $pattern;

    /**
     * @param mixed|null $value
     */
    public function __construct(string $expression, $value = null, string $pattern = '%%%s%%')
    {
        $this->pattern = $pattern;
        parent::__construct($expression, $value ?? new StringValue());
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
