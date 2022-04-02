<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\Specification\Value\BoolValue;
use Spiral\DataGrid\Specification\Value\IntValue;
use Spiral\DataGrid\Specification\Value\StringValue;
use stdClass;

class ValueBetweenTest extends TestCase
{
    /**
     * @dataProvider initValueProvider
     * @param mixed       $expression
     * @param array       $value
     * @param string|null $exception
     */
    public function testInitValue($expression, array $value, ?string $exception): void
    {
        $this->assertTrue(true);
        if ($exception !== null) {
            $this->expectException($exception);
        }

        new Filter\ValueBetween($expression, $value, false, false);
    }

    /**
     * @return iterable
     */
    public function initValueProvider(): iterable
    {
        return [
            //check expressions
            [new IntValue(), ['created', 'updated'], null],
            [1, ['created', 'updated'], null],
            ['value', ['created', 'updated'], null],

            //check values
            [1, [], ValueException::class],
            [1, ['created'], ValueException::class],
            [1, ['created', 'created'], ValueException::class],
            [1, ['created', ['updated']], ValueException::class],
            [1, ['created', 'updated', 'inserted'], ValueException::class],
        ];
    }

    /**
     * @dataProvider withValueProvider
     * @param mixed $expression
     * @param mixed $withValue
     * @param mixed $valid
     */
    public function testWithValue($expression, $withValue, bool $valid): void
    {
        $between = new Filter\ValueBetween($expression, ['created', 'updated'], false, false);

        $this->assertEquals($valid, $between->withValue($withValue) !== null);
    }

    /**
     * @return iterable
     */
    public function withValueProvider(): iterable
    {
        $incorrectValues = [
            'string',
            new IntValue(),
            true,
            false,
            null,
            [],
            [1],
            [1, 2, 3],
        ];

        foreach ($incorrectValues as $incorrectValue) {
            yield[1, $incorrectValue, true];
            yield[new IntValue(), $incorrectValue, false];
        }

        yield from [
            [new BoolValue(), 2, false],
            [1, 2, true],
            [new IntValue(), 2, true],
            [new IntValue(), 'value', false],
        ];
    }

    /**
     * @dataProvider includeProvider
     * @param bool   $includeFrom
     * @param bool   $includeTo
     * @param string $from
     * @param string $to
     */
    public function testInclude(bool $includeFrom, bool $includeTo, string $from, string $to): void
    {
        $between = new Filter\ValueBetween(1, ['created', 'updated'], $includeFrom, $includeTo);
        $filters = $between->getFilters();

        $this->assertNotEmpty($filters);
        $this->assertInstanceOf($from, $filters[0]);
        $this->assertInstanceOf($to, $filters[1]);
    }

    /**
     * @return iterable
     */
    public function includeProvider(): iterable
    {
        return [
            [false, false, Filter\Gt::class, Filter\Lt::class],
            [true, false, Filter\Gte::class, Filter\Lt::class],
            [false, true, Filter\Gt::class, Filter\Lte::class],
            [true, true, Filter\Gte::class, Filter\Lte::class],
        ];
    }

    /**
     * @dataProvider originalProvider
     * @param Filter\ValueBetween $between
     * @param bool                $isOriginal
     * @param string|null         $from
     * @param string|null         $to
     */
    public function testOriginal(
        Filter\ValueBetween $between,
        bool $isOriginal,
        ?string $from,
        ?string $to
    ): void {
        $filters = $between->getFilters(true);

        if ($isOriginal) {
            $this->assertCount(1, $filters);
            $this->assertInstanceOf(Filter\ValueBetween::class, $filters[0]);
        } else {
            $this->assertCount(2, $filters);
            $this->assertInstanceOf($from, $filters[0]);
            $this->assertInstanceOf($to, $filters[1]);
        }
    }

    /**
     * @return iterable
     */
    public function originalProvider(): iterable
    {
        yield from [
            [new Filter\ValueBetween(new IntValue(), ['created', 'updated']), true, null, null],
            [
                new Filter\ValueBetween('field', ['created', 'updated'], false),
                false,
                Filter\Gt::class,
                Filter\Lte::class
            ],
            [
                new Filter\ValueBetween('field', ['created', 'updated'], true, false),
                false,
                Filter\Gte::class,
                Filter\Lt::class
            ],
            [
                new Filter\ValueBetween('field', ['created', 'updated'], false, false),
                false,
                Filter\Gt::class,
                Filter\Lt::class
            ],
        ];

        yield from [
            [new Filter\ValueBetween(new IntValue(), ['created', 'updated']), true, null, null],
            [
                new Filter\ValueBetween(new IntValue(), ['created', 'updated'], false),
                false,
                Filter\Gt::class,
                Filter\Lte::class
            ],
            [
                new Filter\ValueBetween(new IntValue(), ['created', 'updated'], true, false),
                false,
                Filter\Gte::class,
                Filter\Lt::class
            ],
            [
                new Filter\ValueBetween(new IntValue(), ['created', 'updated'], false, false),
                false,
                Filter\Gt::class,
                Filter\Lt::class
            ],
        ];
    }

    public function testGetValue(): void
    {
        $between = new Filter\ValueBetween(1, ['created', 'updated']);
        $this->assertIsInt($between->getValue());
        $this->assertSame(1, $between->withValue(2)->getValue());

        $between = new Filter\ValueBetween(new IntValue(), ['created', 'updated']);
        $this->assertInstanceOf(IntValue::class, $between->getValue());
        $this->assertSame(2, $between->withValue(2)->getValue());

        $between = new Filter\ValueBetween(new StringValue(), ['created', 'updated']);
        $this->assertInstanceOf(StringValue::class, $between->getValue());
        $this->assertSame('3', $between->withValue(3)->getValue());
    }
}
