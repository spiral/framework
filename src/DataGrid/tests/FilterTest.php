<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Filter\Equals;
use Spiral\DataGrid\Specification\Filter\Gte;
use Spiral\DataGrid\Specification\Filter\Lte;
use Spiral\DataGrid\Specification\Filter\Map;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\Value\IntValue;

class FilterTest extends TestCase
{
    public function testEquals(): void
    {
        $equals = new Equals('field', 1);

        $this->assertSame(1, $equals->getValue());
    }

    public function testEqualsApply(): void
    {
        $equals = new Equals('field', new IntValue());

        $this->assertInstanceOf(IntValue::class, $equals->getValue());

        $equals = $equals->withValue('10');
        $this->assertNotNull($equals);

        $this->assertSame(10, $equals->getValue());
    }

    public function testMapFilter(): void
    {
        $map = new Map([
            'from' => new Gte('balance', new IntValue()),
            'to'   => new Lte('balance', new IntValue()),
        ]);

        $this->assertNull($map->withValue(null));
        $this->assertNull($map->withValue('scalar'));

        $this->assertNull($map->withValue([]));
        $this->assertNull($map->withValue(['from' => 1]));

        /** @var Map $map */
        $map = $map->withValue(['from' => 1, 'to' => 2]);
        $this->assertInstanceOf(Map::class, $map);

        /**
         * @var FilterInterface $from
         * @var FilterInterface $to
         */
        ['from' => $from, 'to' => $to] = $map->getFilters();

        $this->assertSame(1, $from->getValue());
        $this->assertSame(2, $to->getValue());
    }
}
