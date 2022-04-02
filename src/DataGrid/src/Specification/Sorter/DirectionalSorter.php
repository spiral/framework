<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

final class DirectionalSorter implements SorterInterface
{
    private ?SorterInterface $sorter = null;
    private ?string $direction = null;

    public function __construct(
        private readonly SorterInterface $asc,
        private readonly SorterInterface $desc
    ) {
    }

    public function withDirection(string $direction): ?SpecificationInterface
    {
        $sorter = clone $this;
        $sorter->direction = $sorter->checkDirection($direction);

        $sorter->sorter = match ($sorter->direction) {
            self::ASC => $sorter->asc->withDirection(self::ASC),
            self::DESC => $sorter->desc->withDirection(self::DESC),
            default => null,
        };

        return $sorter->sorter;
    }

    public function getValue(): ?string
    {
        return $this->direction;
    }

    private function checkDirection(string $direction): ?string
    {
        return match (true) {
            \in_array($direction, ['-1', SORT_DESC], true) => self::DESC,
            \in_array($direction, ['1', SORT_ASC], true) => self::ASC,
            \strtolower($direction) === self::DESC => self::DESC,
            \strtolower($direction) === self::ASC => self::ASC,
            default => null
        };
    }
}
