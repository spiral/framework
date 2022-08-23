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

final class DirectionalSorter implements SorterInterface
{
    /** @var SorterInterface */
    private $asc;

    /** @var SorterInterface */
    private $desc;

    /** @var SorterInterface */
    private $sorter;

    /** @var string|null */
    private $direction;

    public function __construct(SorterInterface $asc, SorterInterface $desc)
    {
        $this->asc = $asc;
        $this->desc = $desc;
    }

    /**
     * @inheritDoc
     */
    public function withDirection($direction): ?SpecificationInterface
    {
        $sorter = clone $this;
        $sorter->direction = $sorter->checkDirection($direction);

        switch ($sorter->direction) {
            case self::ASC:
                $sorter->sorter = $sorter->asc->withDirection(self::ASC);
                break;
            case self::DESC:
                $sorter->sorter = $sorter->desc->withDirection(self::DESC);
                break;
            default:
                $sorter->sorter = null;
        }

        return $sorter->sorter;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->direction;
    }

    /**
     * @param $direction
     */
    private function checkDirection($direction): ?string
    {
        if (is_string($direction)) {
            if (strtolower($direction) === self::DESC) {
                return self::DESC;
            }

            if (strtolower($direction) === self::ASC) {
                return self::ASC;
            }
        }

        if (in_array($direction, ['-1', -1, SORT_DESC], true)) {
            return self::DESC;
        }

        if (in_array($direction, ['1', 1, SORT_ASC], true)) {
            return self::ASC;
        }

        return null;
    }
}
