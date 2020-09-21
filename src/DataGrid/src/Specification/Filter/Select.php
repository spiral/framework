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

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

use function Spiral\DataGrid\hasValue;

/**
 * Complex filter provides the ability to select an intersect of filters by provided value (or array of values)
 */
final class Select extends Group
{
    /**
     * @param array|FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ?SpecificationInterface
    {
        $select = $this->clone($value);
        $value = (array)$value;

        foreach ($this->filters as $name => $filter) {
            $name = (string)$name;
            if (!hasValue($value, $name)) {
                continue;
            }

            $select->filters[$name] = $filter;
        }

        if (empty($select->filters)) {
            return null;
        }

        $filters = array_values($select->filters);

        return count($filters) === 1 ? $filters[0] : new All(...$filters);
    }
}
