<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\QueryWriter;

use Spiral\DataGrid\Specification\Pagination;
use Spiral\Tests\DataGrid\BaseTest;

class WritePaginationTest extends BaseTest
{
    public function testLimit(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Pagination\Limit(10)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" LIMIT 10',
            $select
        );
    }

    public function testLimitOffset(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Pagination\Limit(10),
            new Pagination\Offset(100)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" LIMIT 10 OFFSET 100',
            $select
        );
    }

    public function testPaginate(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            (new Pagination\PagePaginator(25))->withValue([])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" LIMIT 25',
            $select
        );
    }

    public function testPaginate2(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            (new Pagination\PagePaginator(25))->withValue(['page' => 2])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" LIMIT 25 OFFSET 25',
            $select
        );
    }

    public function testPaginate3(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            (new Pagination\PagePaginator(25, [50]))->withValue([
                'page'  => '2',
                'limit' => '50'
            ])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" LIMIT 50 OFFSET 50',
            $select
        );
    }
}
