<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\Value;
use Spiral\DataGrid\Specification\ValueInterface;

class RangeValueTest extends TestCase
{
    /**
     * @dataProvider incorrectBoundariesProvider
     * @param ValueInterface                 $type
     * @param string|null                    $expectedException
     * @param Value\RangeValue\Boundary|null ...$boundaries
     */
    public function testIncorrectBoundaries(
        ValueInterface $type,
        ?string $expectedException,
        ?Value\RangeValue\Boundary ...$boundaries
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        new Value\RangeValue($type, ...$boundaries);

        //we're only looking for expected exceptions
        $this->assertTrue(true);
    }

    /**
     * @return iterable
     */
    public function incorrectBoundariesProvider(): iterable
    {
        $types = [
            Value\AnyValue::class,
            Value\BoolValue::class,
            Value\DatetimeValue::class,
            Value\FloatValue::class,
            Value\IntValue::class,
            Value\NumericValue::class,
            Value\ScalarValue::class,
            Value\StringValue::class,
        ];

        foreach ($types as $type) {
            //both empty boundaries are forbidden
            yield [new $type(), ValueException::class];
        }

        //boundary does not fit the base value type
        yield [new Value\BoolValue(), ValueException::class, null, Value\RangeValue\Boundary::including(2)];
        yield [new Value\BoolValue(), null, null, Value\RangeValue\Boundary::including(1)];

        //boundary should be different
        yield [
            new Value\IntValue(),
            ValueException::class,
            Value\RangeValue\Boundary::including(2),
            Value\RangeValue\Boundary::including(2)
        ];
        yield [
            new Value\IntValue(),
            ValueException::class,
            Value\RangeValue\Boundary::excluding(2),
            Value\RangeValue\Boundary::including(2)
        ];
        yield [
            new Value\IntValue(),
            null,
            Value\RangeValue\Boundary::including(1),
            Value\RangeValue\Boundary::including(2)
        ];
    }

    /**
     * @dataProvider swapBoundariesProvider
     * @param mixed $start
     * @param mixed $end
     */
    public function testBoundariesSwap($start, $end): void
    {
        $range = new Value\RangeValue(
            new Value\IntValue(),
            Value\RangeValue\Boundary::including($start),
            Value\RangeValue\Boundary::including($end)
        );

        $this->assertTrue($range->accepts($start));
        $this->assertTrue($range->accepts($end));
    }

    /**
     * @return iterable
     */
    public function swapBoundariesProvider(): iterable
    {
        return [
            [1, 2],
            [2, 1],
        ];
    }

    /**
     * @dataProvider acceptsProvider
     * @param mixed                          $value
     * @param bool                           $expected
     * @param Value\RangeValue\Boundary|null ...$boundaries
     */
    public function testAccepts($value, bool $expected, ?Value\RangeValue\Boundary ...$boundaries): void
    {
        $enum = new Value\RangeValue(new Value\IntValue(), ...$boundaries);
        $this->assertSame($expected, $enum->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            //less than end boundary
            [1, true, null, Value\RangeValue\Boundary::excluding(2)],
            [1, true, null, Value\RangeValue\Boundary::including(2)],

            //equals to end boundary
            [1, true, null, Value\RangeValue\Boundary::including(1)],
            [1, false, null, Value\RangeValue\Boundary::excluding(1)],

            //greater than end boundary
            [3, false, null, Value\RangeValue\Boundary::excluding(2)],
            [3, false, null, Value\RangeValue\Boundary::including(2)],

            //less than start boundary
            [1, false, Value\RangeValue\Boundary::excluding(2), null],
            [1, false, Value\RangeValue\Boundary::including(2), null],

            //equals to start boundary
            [1, true, Value\RangeValue\Boundary::including(1), null],
            [1, false, Value\RangeValue\Boundary::excluding(1), null],

            //greater than start boundary
            [1, true, Value\RangeValue\Boundary::excluding(0), null],
            [1, true, Value\RangeValue\Boundary::including(0), null],

            //check start boundary
            [1, true, Value\RangeValue\Boundary::including(0), Value\RangeValue\Boundary::including(2)],
            [1, true, Value\RangeValue\Boundary::excluding(0), Value\RangeValue\Boundary::including(2)],
            [1, true, Value\RangeValue\Boundary::including(1), Value\RangeValue\Boundary::including(2)],
            [1, false, Value\RangeValue\Boundary::excluding(1), Value\RangeValue\Boundary::including(2)],

            //check end boundary
            [2, true, Value\RangeValue\Boundary::including(2), Value\RangeValue\Boundary::including(3)],
            [2, true, Value\RangeValue\Boundary::including(2), Value\RangeValue\Boundary::excluding(3)],
            [3, true, Value\RangeValue\Boundary::including(2), Value\RangeValue\Boundary::including(3)],
            [3, false, Value\RangeValue\Boundary::including(2), Value\RangeValue\Boundary::excluding(3)],
        ];
    }
}
