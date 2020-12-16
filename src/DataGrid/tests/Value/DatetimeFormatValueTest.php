<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use Spiral\DataGrid\Specification\Value\DatetimeFormatValue;
use Spiral\Tests\Files\TestCase;

class DatetimeFormatValueTest extends TestCase
{
    /**
     * @dataProvider invalidProvider
     * @param string $readFrom
     * @param string $convertInto
     * @param        $input
     */
    public function testInvalid(string $readFrom, string $convertInto, $input): void
    {
        $value = new DatetimeFormatValue($readFrom, $convertInto);
        $this->assertFalse($value->accepts($input));
        $this->assertNull($value->convert($input));
    }

    public function invalidProvider(): iterable
    {
        return [
            ['Y-m-d', 'Y-m-d', '12345'],
            ['bad', 'Y-m-d', '12345'],
            ['Y-m-d', 'bad', '12345'],
            ['bad', 'bad', '12345'],
        ];
    }

    public function testValid(): void
    {
        $value = new DatetimeFormatValue('Ymd', 'Y-m-d');
        $this->assertTrue($value->accepts('20201231'));
        $this->assertEquals('2020-12-31', $value->convert('20201231'));
    }
}
