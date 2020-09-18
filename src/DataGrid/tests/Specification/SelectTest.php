<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Filter;

class SelectTest extends TestCase
{
    /**
     * @dataProvider emptyProvider
     * @param mixed $value
     */
    public function testEmptyList($value): void
    {
        $select = new Filter\Select([]);
        $this->assertNull($select->withValue($value));
    }

    /**
     * @return iterable
     */
    public function emptyProvider(): iterable
    {
        return [
            [''],
            [['']],
            [null],
            [[null]],
            [12345],
            [[12345]],
            ['test'],
            [['test']],
            [[]],
        ];
    }

    /**
     * @dataProvider emptyProvider
     * @param mixed $value
     */
    public function testUnknownValue($value): void
    {
        $this->assertNull($this->filter()->withValue($value));
    }

    public function testScalarValue(): void
    {
        /** @var Filter\Select $filter */
        $filter = $this->filter()->withValue(1);

        $this->assertInstanceOf(Filter\Equals::class, $filter);
    }

    public function testSingleValue(): void
    {
        /** @var Filter\Select $filter */
        $filter = $this->filter()->withValue('1');

        $this->assertInstanceOf(Filter\Equals::class, $filter);
    }

    public function testArrayValue(): void
    {
        /** @var Filter\Select $filter */
        $filter = new Filter\Select([
            'one' => new Filter\Equals('name', 'value'),
            'two' => new Filter\Any(
                new Filter\Equals('price', 2),
                new Filter\Gt('quantity', 5)
            )
        ]);
        $filter = $filter->withValue(['one', 'two']);

        $this->assertNotNull($filter);
        $this->assertInstanceOf(Filter\All::class, $filter);
        $this->assertCount(2, $filter->getFilters());
        $this->assertInstanceOf(Filter\Equals::class, array_values($filter->getFilters())[0]);
        $this->assertInstanceOf(Filter\Any::class, array_values($filter->getFilters())[1]);
    }

    public function testNoKeyValues(): void
    {
        $select = new Filter\Select([
            new Filter\Equals('name', 'value'),
            new Filter\Any(
                new Filter\Equals('price', 2),
                new Filter\Gt('quantity', 5)
            )
        ]);

        $filter = $select->withValue(1);
        $this->assertInstanceOf(Filter\Any::class, $filter);
    }

    /**
     * @return Filter\Select
     */
    private function filter(): Filter\Select
    {
        return new Filter\Select([
            '1' => new Filter\Equals('name', 'value'),
            '2' => new Filter\Any(
                new Filter\Equals('price', 2),
                new Filter\Gt('quantity', 5)
            )
        ]);
    }
}
