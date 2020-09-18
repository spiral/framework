<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Pagination;

use Spiral\DataGrid\SpecificationInterface;

final class Limit implements SpecificationInterface
{
    /** @var int */
    private $value;

    /**
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
