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
use Spiral\DataGrid\Specification\Pagination\Limit;
use Spiral\DataGrid\Specification\Pagination\Offset;
use Spiral\DataGrid\Specification\Pagination\PagePaginator;

class PaginatorTest extends TestCase
{
    /**
     * @dataProvider getValueProvider
     * @param array $expected
     * @param       $value
     */
    public function testLimitPaginator(array $expected, $value): void
    {
        $paginator = new PagePaginator(25, [50, 100]);
        $this->assertSame($expected, $paginator->withValue($value)->getValue());
    }

    /**
     * @return iterable
     */
    public function getValueProvider(): iterable
    {
        return [
            [['limit' => 25, 'page' => 1], null],
            [['limit' => 25, 'page' => 2], ['page' => 2]],
            [['limit' => 100, 'page' => 2], ['page' => 2, 'limit' => 100]],
            [['limit' => 100, 'page' => 1], ['limit' => 100]],
        ];
    }

    /**
     * @dataProvider specificationsProvider
     * @param                        $value
     * @param array                  $expected
     */
    public function testSpecifications($value, array $expected): void
    {
        /** @var PagePaginator $paginator */
        $paginator = new PagePaginator(25, [50, 100]);
        $paginator = $paginator->withValue($value);

        $specifications = [];
        foreach ($paginator->getSpecifications() as $specification) {
            $specifications[] = get_class($specification);
        }

        $this->assertSame($expected, $specifications);
    }

    /**
     * @return iterable
     */
    public function specificationsProvider(): iterable
    {
        return [
            [null, [Limit::class]],
            [['page' => 1], [Limit::class]],
            [['page' => 1, 'limit' => 50], [Limit::class]],
            [['limit' => 50], [Limit::class]],
            [['limit' => 25, 'page' => 2], [Limit::class, Offset::class]],
            [['limit' => 100, 'page' => 2], [Limit::class, Offset::class]],
        ];
    }
}
