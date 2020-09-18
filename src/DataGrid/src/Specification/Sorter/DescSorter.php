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

final class DescSorter extends AbstractSorter
{
    /**
     * @inheritDoc
     * @return string
     */
    public function getValue(): string
    {
        return self::DESC;
    }
}
