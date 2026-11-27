<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;

/**
 * Declares its nested array only in the mapping schema, so the elements are built by
 * {@see \Spiral\Filters\Model\Schema\InputMapper} alone.
 */
final class DefinitionListFilter extends Filter implements HasFilterDefinition
{
    public function filterDefinition(): FilterDefinitionInterface
    {
        return new ItemsDefinition();
    }
}
