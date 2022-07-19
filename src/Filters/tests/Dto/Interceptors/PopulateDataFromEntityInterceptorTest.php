<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Dto\Interceptors;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Dto\Filter;
use Spiral\Filters\Dto\FilterBag;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Dto\Interceptors\PopulateDataFromEntityInterceptor;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTest;

final class PopulateDataFromEntityInterceptorTest extends BaseTest
{
    private PopulateDataFromEntityInterceptor $interceptor;

    public function setUp(): void
    {
        parent::setUp();

        $this->interceptor = new PopulateDataFromEntityInterceptor();
    }

    public function testDataShouldNotBeSetWhenNotFilterObject(): void
    {
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')->once()->andReturn($filter = m::mock(FilterInterface::class));

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [
            'filterBag' => new FilterBag($filter, m::mock(AbstractEntity::class), [], [])
        ], $core));
    }

    public function testDataShouldBeSetWhenFilterObject(): void
    {
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')->once()->andReturn($filter = m::mock(Filter::class));

        $bag = new FilterBag($filter, $entity = m::mock(AbstractEntity::class), [], []);

        $entity->shouldReceive('toArray')->once()->andReturn($data = ['foo' => 'bar']);
        $filter->shouldReceive('setData')->once()->with($data);

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', ['filterBag' => $bag], $core));
    }
}
