<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use Mockery as m;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;
use Spiral\Models\AbstractEntity;

final class FilterBagTest extends BaseTest
{
    public function testBagShouldBeCreated(): void
    {
        $bag = new FilterBag(
            $filter = m::mock(FilterInterface::class),
            $entity = m::mock(AbstractEntity::class),
            $schema = ['foo' => 'bar',]
        );

        $this->assertSame($filter, $bag->filter);
        $this->assertSame($entity, $bag->entity);
        $this->assertSame($schema, $bag->schema);
    }
}
