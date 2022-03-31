<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Pagination;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\Specification\Value;
use Spiral\DataGrid\Specification\Value\EnumValue;
use Spiral\DataGrid\SpecificationInterface;

final class PagePaginator implements SequenceInterface, FilterInterface
{
    private readonly EnumValue $limitValue;
    private int $page = 1;

    public function __construct(
        private int $limit,
        array $allowedLimits = []
    ) {
        $allowedLimits[] = $limit;

        \sort($allowedLimits);

        $this->limitValue = new EnumValue(new Value\IntValue(), ...$allowedLimits);
    }

    public function withValue(mixed $value): ?SpecificationInterface
    {
        $paginator = clone $this;
        if (!\is_array($value)) {
            return $paginator;
        }

        if (isset($value['limit']) && $paginator->limitValue->accepts($value['limit'])) {
            $paginator->limit = $paginator->limitValue->convert($value['limit']);
        }

        if (isset($value['page']) && \is_numeric($value['page'])) {
            $paginator->page = \max((int)$value['page'], 1);
        }

        return $paginator;
    }

    public function getSpecifications(): array
    {
        $specifications = [new Limit($this->limit)];
        if ($this->page > 1) {
            $specifications[] = new Offset($this->limit * ($this->page - 1));
        }

        return $specifications;
    }

    public function getValue(): array
    {
        return [
            'limit' => $this->limit,
            'page'  => $this->page,
        ];
    }
}
