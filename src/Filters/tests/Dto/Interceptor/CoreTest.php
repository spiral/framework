<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Dto\Interceptor;

use Mockery as m;
use Spiral\Filters\Dto\FilterBag;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Dto\Interceptor\Core;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTest;

final class CoreTest extends BaseTest
{
    public function testCall(): void
    {
        $core = new Core();

        $filter = m::mock(FilterInterface::class);

        $this->assertSame($filter, $core->callAction('foo', 'bar', [
            'filterBag' => new FilterBag($filter, m::mock(AbstractEntity::class), [], [])
        ]));
    }
}
