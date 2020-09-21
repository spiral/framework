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

final class SorterSet implements SorterInterface
{
    /** @var SorterInterface[] */
    private $sorters;

    /**
     * @param SorterInterface ...$sorters
     */
    public function __construct(SorterInterface ...$sorters)
    {
        $this->sorters = $sorters;
    }

    /**
     * @inheritDoc
     */
    public function withDirection($direction): SpecificationInterface
    {
        $sorter = clone $this;
        $sorter->sorters = [];

        foreach ($this->sorters as $s) {
            $sorter->sorters[] = $s->withDirection($direction);
        }

        return $sorter;
    }

    /**
     * @return SorterInterface[]
     */
    public function getSorters(): array
    {
        return $this->sorters;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getValue(): string
    {
        return '1';
    }
}
