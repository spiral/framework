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

class BetweenTest extends TestCase
{
    /**
     * @dataProvider initValueProvider
     * @param mixed       $value
     * @param string|null $exception
     */
    public function testInitValue($value, ?string $exception): void
    {
        $this->assertTrue(true);
        if ($exception !== null) {
            $this->expectException($exception);
        }

        new Filter\Between('field', $value, false, false);
    }

    /**
     * @return iterable
     */
    public function initValueProvider(): iterable
    {
        return [
            [new IntValue(), null],
            [[1, 2], null],
            [[3, 2], null],
            [[], ValueException::class],
            [[1], ValueException::class],
            [[2, 2], ValueException::class],
            [[1, 2, 3], ValueException::class],
        ];
    }

    /**
     * @dataProvider withValueProvider
     * @param mixed $value
     * @param mixed $withValue
     * @param mixed $valid
     */
    public function testWithValue($value, $withValue, bool $valid): void
    {
        $between = new Filter\Between('field', $value, false, false);

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
            1,
            [],
            [1],
            [1, 2, 3],
            new stdClass()
        ];

        foreach ($incorrectValues as $incorrectValue) {
            yield[[1, 2], $incorrectValue, true];
            yield[new IntValue(), $incorrectValue, false];
        }

        yield from [
            [new BoolValue(), 2, false],
            [[1, 2], [2, 3], true],
            [new IntValue(), [2, 3], true],
            [new IntValue(), [3, 2], true],
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
        $between = new Filter\Between('field', new IntValue(), $includeFrom, $includeTo);
        $between = $between->withValue([2, 3]);
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
     * @dataProvider swapBoundariesProvider
     * @param Filter\Between $between
     */
    public function testSwapBoundaries(Filter\Between $between): void
    {
        $filters = $between->getFilters();

        $this->assertEquals(2, $filters[0]->getValue());
        $this->assertEquals(3, $filters[1]->getValue());
    }

    /**
     * @return iterable
     */
    public function swapBoundariesProvider(): iterable
    {
        yield [new Filter\Between('field', [3, 2])];

        $between = new Filter\Between('field', new IntValue());
        yield [$between->withValue([3, 2])];
    }

    /**
     * @dataProvider originalProvider
     * @param Filter\Between $between
     * @param bool           $isOriginal
     * @param string|null    $from
     * @param string|null    $to
     */
    public function testOriginal(
        Filter\Between $between,
        bool $isOriginal,
        ?string $from,
        ?string $to
    ): void {
        $filters = $between->getFilters(true);

        if ($isOriginal) {
            $this->assertCount(1, $filters);
            $this->assertInstanceOf(Filter\Between::class, $filters[0]);
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
        return [
            [new Filter\Between('field', new IntValue()), true, null, null],
            [new Filter\Between('field', [1, 2], false), false, Filter\Gt::class, Filter\Lte::class],
            [new Filter\Between('field', [1, 2], true, false), false, Filter\Gte::class, Filter\Lt::class],
            [new Filter\Between('field', [1, 2], false, false), false, Filter\Gt::class, Filter\Lt::class],
        ];
    }

    public function testGetValue(): void
    {
        $between = new Filter\Between('field', [1, 2]);
        $this->assertIsArray($between->getValue());
        $this->assertEquals([1, 2], $between->withValue([3, 4])->getValue());

        $between = new Filter\Between('field', new StringValue());
        $this->assertInstanceOf(StringValue::class, $between->getValue());
        $this->assertSame(['3', '4'], $between->withValue([3, 4])->getValue());
    }
}
