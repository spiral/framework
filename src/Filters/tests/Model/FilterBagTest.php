<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Mockery as m;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTest;

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
