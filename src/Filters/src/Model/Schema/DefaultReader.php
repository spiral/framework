<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\HasFilterDefinition;

/**
 * @internal
 * Read filter based on array definition and return schema and setters.
 */
final class DefaultReader implements ReaderInterface
{
    /**
     * @return array{0: array, 1: array}
     */
    public function read(FilterInterface $filter): array
    {
        return $filter instanceof HasFilterDefinition
            ? [$filter->filterDefinition()->mappingSchema(), []]
            : [[], []];
    }
}
