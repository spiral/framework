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

use Spiral\DataGrid\Specification\Sorter;
use Spiral\Tests\DataGrid\BaseTest;

class WriteSorterTest extends BaseTest
{
    public function testSort(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Sorter\AscSorter('balance')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" ASC',
            $select
        );
    }
    public function testExpressionSort(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Sorter\AscSorter('ISNULL(balance)')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY ISNULL("balance") ASC',
            $select
        );
    }

    public function testSortDesc(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Sorter\DescSorter('balance')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" DESC',
            $select
        );
    }

    public function testSortMultiple(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Sorter\AscSorter('balance'),
            new Sorter\AscSorter('credits'),
            new Sorter\DescSorter('attempts')
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" ASC, "credits" ASC, "attempts" DESC',
            $select
        );
    }

    public function testUnary(): void
    {
        $unary = new Sorter\SorterSet(
            new Sorter\AscSorter('balance'),
            new Sorter\AscSorter('credits'),
            new Sorter\DescSorter('attempts')
        );
        $select = $this->compile(
            $this->initQuery(),
            $unary
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" ASC, "credits" ASC, "attempts" DESC',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            new Sorter\SorterSet($unary)
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" ASC, "credits" ASC, "attempts" DESC',
            $select
        );
    }

    /**
     * @dataProvider binarySortProvider
     * @param mixed      $direction
     * @param mixed|null $resultDirection
     */
    public function testBinary($direction, $resultDirection = null): void
    {
        $sorter = new Sorter\DirectionalSorter(
            new Sorter\SorterSet(
                new Sorter\AscSorter('balance'),
                new Sorter\AscSorter('credits')
            ),
            new Sorter\SorterSet(
                new Sorter\DescSorter('balance'),
                new Sorter\DescSorter('credits')
            )
        );

        if ($resultDirection === null) {
            $this->assertNull($sorter->withDirection($direction));
        } else {
            $select = $this->compile(
                $this->initQuery(),
                $sorter->withDirection($direction)
            );

            $this->assertEqualSQL(
                sprintf(
                    'SELECT * FROM "users" ORDER BY "balance" %s, "credits" %s',
                    $resultDirection,
                    $resultDirection
                ),
                $select
            );
        }
    }

    public function testMixedBinary(): void
    {
        $sorter = new Sorter\DirectionalSorter(
            new Sorter\SorterSet(
                new Sorter\AscSorter('balance'),
                new Sorter\DescSorter('credits')
            ),
            new Sorter\SorterSet(
                new Sorter\DescSorter('balance'),
                new Sorter\AscSorter('credits')
            )
        );

        $select = $this->compile(
            $this->initQuery(),
            $sorter->withDirection('asc')
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" ASC, "credits" DESC',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            $sorter->withDirection('desc')
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" ORDER BY "balance" DESC, "credits" ASC',
            $select
        );
    }

    /**
     * @dataProvider binarySortProvider
     * @param mixed      $direction
     * @param mixed|null $resultDirection
     */
    public function testSortBinary($direction, $resultDirection = null): void
    {
        $sorter = new Sorter\Sorter('balance', 'credits');

        if ($resultDirection === null) {
            $this->assertNull($sorter->withDirection($direction));
        } else {
            $select = $this->compile(
                $this->initQuery(),
                $sorter->withDirection($direction)
            );

            $this->assertEqualSQL(
                sprintf(
                    'SELECT * FROM "users" ORDER BY "balance" %s, "credits" %s',
                    $resultDirection,
                    $resultDirection
                ),
                $select
            );
        }
    }

    public function binarySortProvider(): array
    {
        return [
            ['asc', 'ASC'],
            ['1', 'ASC'],
            [1, 'ASC'],
            [SORT_ASC, 'ASC'],

            ['desc', 'DESC'],
            ['-1', 'DESC'],
            [-1, 'DESC'],
            [SORT_DESC, 'DESC'],

            [123, null],
        ];
    }
}
