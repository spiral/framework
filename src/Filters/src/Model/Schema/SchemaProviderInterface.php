<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Model\FilterInterface;

/**
 * Read filter and return merged schema and setters from all sources.
 */
interface SchemaProviderInterface
{
    public function getSchema(FilterInterface $filter): array;

    public function getSetters(FilterInterface $filter): array;
}
