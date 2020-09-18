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

final class Sorter implements SorterInterface
{
    /** @var DirectionalSorter */
    private $sorter;

    /**
     * @param string ...$expressions
     */
    public function __construct(string ...$expressions)
    {
        $this->sorter = new DirectionalSorter(new AscSorter(...$expressions), new DescSorter(...$expressions));
    }

    /**
     * @inheritDoc
     */
    public function withDirection($direction): ?SpecificationInterface
    {
        $sorter = clone $this;

        return $sorter->sorter->withDirection($direction);
    }

    /**
     * @inheritDoc
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->sorter->getValue();
    }
}
