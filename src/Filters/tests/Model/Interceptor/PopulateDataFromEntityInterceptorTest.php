<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Interceptor;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\Interceptor\PopulateDataFromEntityInterceptor;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTestCase;

final class PopulateDataFromEntityInterceptorTest extends BaseTestCase
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

        self::assertSame($filter, $this->interceptor->process('foo', 'bar', [
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

        self::assertSame($filter, $this->interceptor->process('foo', 'bar', ['filterBag' => $bag], $core));
    }
}
