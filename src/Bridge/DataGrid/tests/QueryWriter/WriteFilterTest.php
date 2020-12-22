<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\QueryWriter;

use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\Specification\Value\IntValue;
use Spiral\Tests\DataGrid\BaseTest;

class WriteFilterTest extends BaseTest
{
    public function testEquals(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Equals('name', 'Antony')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "name" = \'Antony\'',
            $select
        );
    }

    public function testEqualsNoValue(): void
    {
        $this->expectException(CompilerException::class);
        $this->compile(
            $this->initQuery(),
            new Filter\Equals('balance', new IntValue())
        );
    }

    public function testLike(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Like('name', 'Antony')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "name" LIKE \'%Antony%\'',
            $select
        );
    }

    public function testLikePattern(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Like('name', 'Antony', '%%%s')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "name" LIKE \'%Antony\'',
            $select
        );
    }

    public function testAll(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\All(
                new Filter\Equals('name', 'Antony'),
                new Filter\Equals('balance', 100)
            )
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ("name" = \'Antony\' AND "balance" = 100)',
            $select
        );
    }

    public function testAny(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Any(
                new Filter\Equals('name', 'Antony'),
                new Filter\Equals('balance', 100)
            )
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE (("name" = \'Antony\') OR ("balance" = 100))',
            $select
        );
    }

    public function testAnyAndAnyQuery(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\All(
                new Filter\Any(
                    new Filter\Equals('a', 'aa'),
                    new Filter\Equals('b', 'bb')
                ),
                new Filter\Any(
                    new Filter\Equals('c', 'cc'),
                    new Filter\Equals('d', 'dd')
                )
            )
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ((("a" = \'aa\') OR ("b" = \'bb\')) AND (("c" = \'cc\') OR ("d" = \'dd\')))',
            $select
        );
    }

    public function testInArray(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\InArray('id', [1, 2, 3])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "id" IN (1,2,3)',
            $select
        );
    }

    public function testNotInArray(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\NotInArray('id', [1, 2, 3])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "id" NOT IN (1,2,3)',
            $select
        );
    }

    public function testGt(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Gt('value', 5)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "value" > 5',
            $select
        );
    }

    public function testGte(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Gte('value', 5)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "value" >= 5',
            $select
        );
    }

    public function testLt(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Lt('value', 5)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "value" < 5',
            $select
        );
    }

    public function testLte(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Lte('value', 5)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "value" <= 5',
            $select
        );
    }

    public function testMap(): void
    {
        $this->assertTrue(true);
    }

    public function testNotEquals(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\NotEquals('name', 'Antony')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "name" != \'Antony\'',
            $select
        );
    }

    public function testFragmentInjection(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\FragmentInjectionFilter(new Filter\Equals('date(created)', '2020-06-06'))
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE date(created) = \'2020-06-06\'',
            $select
        );
    }

    public function testExpressionInjection(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\ExpressionInjectionFilter(new Filter\Equals('date(created)', '2020-06-06'))
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE date("created") = \'2020-06-06\'',
            $select
        );
    }
}
