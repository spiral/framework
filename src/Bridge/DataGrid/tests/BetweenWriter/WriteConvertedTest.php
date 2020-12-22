<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\BetweenWriter;

use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\Specification\Value\StringValue;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\Writer\BetweenWriter;
use Spiral\DataGrid\Writer\QueryWriter;
use Spiral\Tests\DataGrid\BaseTest;

class WriteConvertedTest extends BaseTest
{
    public function testBetween(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Between('field', [1, 2])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ("field" >= 1 AND "field" <= 2)',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            (new Filter\Between('field', new StringValue()))->withValue([1, 2])
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ("field" >= \'1\' AND "field" <= \'2\')',
            $select
        );
    }

    public function testExpressionBetween(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\FragmentInjectionFilter(new Filter\Between('ROUND(field)', [1, 2]))
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE (ROUND(field) >= 1 AND ROUND(field) <= 2)',
            $select
        );
    }

    public function testValueBetween(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\ValueBetween(12, ['created', 'updated'])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ("updated" >= 12 AND "created" <= 12)',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            (new Filter\ValueBetween(new StringValue(), ['created', 'updated']))->withValue(12)
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE ("updated" >= \'12\' AND "created" <= \'12\')',
            $select
        );
    }

    /**
     * @inheritDoc
     */
    protected function compile($source, SpecificationInterface ...$specifications)
    {
        $compiler = new Compiler();
        $compiler->addWriter(new BetweenWriter(false));
        $compiler->addWriter(new QueryWriter());

        return $compiler->compile($source, ...$specifications);
    }
}
