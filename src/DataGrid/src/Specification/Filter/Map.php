<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

use function Spiral\DataGrid\hasKey;

/**
 * Complex filter provides the ability to distribute complex array value across multiple
 * nested filters.
 */
final class Map extends Group
{
    /**
     * @param array|FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function withValue(mixed $value): ?SpecificationInterface
    {
        if (!\is_array($value)) {
            // only array values are expected
            return null;
        }

        $map = $this->clone($value);
        foreach ($this->filters as $name => $filter) {
            $name = (string)$name;
            if (!hasKey($value, $name)) {
                // all values must be provided
                return null;
            }

            $applied = $filter->withValue($value[$name]);
            if ($applied === null) {
                return null;
            }

            $map->filters[$name] = $applied;
        }

        return !empty($map->filters) ? $map : null;
    }
}
