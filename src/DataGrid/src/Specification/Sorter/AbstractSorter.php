<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class AbstractSorter implements SorterInterface
{
    /** @var array */
    private $expressions;

    /**
     * @param string ...$expressions
     */
    public function __construct(string ...$expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @inheritDoc
     */
    public function withDirection($direction): SpecificationInterface
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }
}
