<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value\Accessor;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Value;
use Spiral\DataGrid\Specification\Value\Accessor;
use Spiral\DataGrid\Specification\ValueInterface;
use Spiral\Tests\DataGrid\Fixture\Add;
use Spiral\Tests\DataGrid\Fixture\Multiply;

class AccessorTest extends TestCase
{
    /**
     * Accessors go outside-inside order, the parent is the first
     */
    public function testOrder(): void
    {
        $int = new Value\IntValue();
        $this->assertSame(6, (new Multiply(new Add($int, 2), 2))->convert(2));
        $this->assertSame(12, (new Multiply(new Add($int, 3), 3))->convert(3));

        $this->assertSame(8, (new Add(new Multiply($int, 2), 2))->convert(2));
        $this->assertSame(18, (new Add(new Multiply($int, 3), 3))->convert(3));
    }

    /**
     * @dataProvider accessorProvider
     * @param string $accessor
     */
    public function testAccepts(string $accessor): void
    {
        $numeric = new Value\NumericValue();
        $this->assertTrue($this->create($accessor, $numeric)->accepts('abc'));
        $this->assertTrue($this->create($accessor, $numeric)->accepts(123));

        $scalar = new Value\ScalarValue();
        $this->assertTrue($this->create($accessor, $scalar)->accepts(123));
        $this->assertFalse($this->create($accessor, $scalar)->accepts([]));

        $this->assertTrue($this->create($accessor, new Value\AnyValue())->accepts([]));
    }

    public function accessorProvider(): iterable
    {
        return [
            [Accessor\ToUpper::class],
            [Accessor\ToLower::class],
        ];
    }

    public function testToUpper(): void
    {
        $string = new Value\StringValue();
        $scalar = new Value\ScalarValue();

        $this->assertSame('ABC', (new Accessor\ToUpper($string))->convert('abc'));
        $this->assertSame('ABC', (new Accessor\ToUpper($string))->convert('ABC'));
        $this->assertSame('123', (new Accessor\ToUpper($string))->convert(123));
        $this->assertSame(123, (new Accessor\ToUpper($scalar))->convert(123));
    }

    public function testToLower(): void
    {
        $string = new Value\StringValue();
        $scalar = new Value\ScalarValue();

        $this->assertSame('abc', (new Accessor\ToLower($string))->convert('abc'));
        $this->assertSame('abc', (new Accessor\ToLower($string))->convert('ABC'));
        $this->assertSame('123', (new Accessor\ToLower($string))->convert(123));
        $this->assertSame(123, (new Accessor\ToLower($scalar))->convert(123));
    }

    /**
     * @param string         $accessor
     * @param ValueInterface $value
     * @return Accessor\Accessor
     */
    private function create(string $accessor, ValueInterface $value): Accessor\Accessor
    {
        return new $accessor($value);
    }
}
