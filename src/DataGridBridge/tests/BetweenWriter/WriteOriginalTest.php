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
use Spiral\DataGrid\Specification\Value\IntValue;
use Spiral\DataGrid\Specification\Value\StringValue;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\Writer\BetweenWriter;
use Spiral\DataGrid\Writer\QueryWriter;
use Spiral\Tests\DataGrid\BaseTest;

class WriteOriginalTest extends BaseTest
{
    public function testBetween(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Between('field', [1, 2])
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "field" BETWEEN 1 AND 2',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            (new Filter\Between('field', new StringValue()))->withValue([1, 3])
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE "field" BETWEEN \'1\' AND \'3\'',
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
            'SELECT * FROM "users" WHERE ROUND(field) BETWEEN 1 AND 2',
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
            'SELECT * FROM "users" WHERE 12 BETWEEN \'created\' AND \'updated\'',
            $select
        );

        $select = $this->compile(
            $this->initQuery(),
            (new Filter\ValueBetween(new IntValue(), ['created', 'updated']))->withValue('12')
        );
        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE 12 BETWEEN \'created\' AND \'updated\'',
            $select
        );
    }

    /**
     * @inheritDoc
     */
    protected function compile($source, SpecificationInterface ...$specifications)
    {
        $compiler = new Compiler();
        $compiler->addWriter(new BetweenWriter(true));
        $compiler->addWriter(new QueryWriter());

        return $compiler->compile($source, ...$specifications);
    }
}
