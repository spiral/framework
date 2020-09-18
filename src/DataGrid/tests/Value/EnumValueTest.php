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

class EnumValueTest extends TestCase
{
    /**
     * @dataProvider incorrectEnumProvider
     * @param ValueInterface $type
     * @param string|null    $expectedException
     * @param mixed          ...$values
     */
    public function testIncorrectEnum(ValueInterface $type, ?string $expectedException, ...$values): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        new Value\EnumValue($type, ...$values);

        //we're only looking for expected exceptions
        $this->assertTrue(true);
    }

    /**
     * @return iterable
     */
    public function incorrectEnumProvider(): iterable
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
            yield [new $type(), ValueException::class];
        }

        yield [new Value\StringValue(), ValueException::class, [1, '2']];
    }

    /**
     * @dataProvider acceptsProvider
     * @param mixed $value
     * @param bool  $expected
     */
    public function testAccepts($value, bool $expected): void
    {
        $enum = new Value\EnumValue(new Value\StringValue(), '1', '2');
        $this->assertSame($expected, $enum->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            [1, true],
            ['1', true],
            ['3', false]
        ];
    }

    public function testNested(): void
    {
        $this->expectException(ValueException::class);
        new Value\EnumValue(new Value\EnumValue(new Value\StringValue(), 'a', 'b'), 'c', 'd');

        //we're only looking for expected exceptions
        $this->assertTrue(true);
    }
}
