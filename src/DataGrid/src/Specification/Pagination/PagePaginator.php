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

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\Specification\Value;
use Spiral\DataGrid\SpecificationInterface;

final class PagePaginator implements SequenceInterface, FilterInterface
{
    /** @var Value\EnumValue */
    private $limitValue;

    /** @var int */
    private $limit;

    /** @var int */
    private $page;

    /**
     * @param int   $defaultLimit
     * @param array $allowedLimits
     */
    public function __construct(int $defaultLimit, array $allowedLimits = [])
    {
        $this->limit = $defaultLimit;
        $this->page = 1;

        $allowedLimits[] = $defaultLimit;

        sort($allowedLimits);

        $this->limitValue = new Value\EnumValue(new Value\IntValue(), ...$allowedLimits);
    }

    /**
     * @param mixed $value
     * @return FilterInterface|null
     */
    public function withValue($value): ?SpecificationInterface
    {
        $paginator = clone $this;
        if (!is_array($value)) {
            return $paginator;
        }

        if (isset($value['limit']) && $paginator->limitValue->accepts($value['limit'])) {
            $paginator->limit = $paginator->limitValue->convert($value['limit']);
        }

        if (isset($value['page']) && is_numeric($value['page'])) {
            $paginator->page = max((int)$value['page'], 1);
        }

        return $paginator;
    }

    /**
     * @inheritDoc
     */
    public function getSpecifications(): array
    {
        $specifications = [new Limit($this->limit)];
        if ($this->page > 1) {
            $specifications[] = new Offset($this->limit * ($this->page - 1));
        }

        return $specifications;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getValue(): array
    {
        return [
            'limit' => $this->limit,
            'page'  => $this->page,
        ];
    }
}
