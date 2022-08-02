<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification\Filter\Postgres;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Filter\Postgres\ILike;

final class ILikeTest extends TestCase
{
    public function testWithValue(): void
    {
        $filter = new ILike('foo');

        $filter = $filter->withValue('bar');
        $this->assertInstanceOf(ILike::class, $filter);
    }
}
