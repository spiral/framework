<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Interceptor;

use Mockery as m;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTestCase;

final class CoreTest extends BaseTestCase
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
