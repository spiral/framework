<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Tests\Filters\Fixtures\LeafFilter;

final class ItemsDefinition implements FilterDefinitionInterface
{
    public function mappingSchema(): array
    {
        return ['items' => [LeafFilter::class, 'items.*']];
    }
}
