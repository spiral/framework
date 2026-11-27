<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Exception\ConfigException;
use Spiral\Filters\Config\FiltersConfig;

final class FiltersConfigTest extends TestCase
{
    public static function validLimitProvider(): iterable
    {
        yield 'int' => [5000, 5000];
        yield 'string of digits, as env() returns it' => ['5000', 5000];
        yield 'false disables' => [false, null];
        yield 'null keeps the default' => [null, 2000];
    }

    public static function invalidLimitProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'zero string' => ['0'];
        yield 'negative' => [-1];
        yield 'negative string' => ['-1'];
        yield 'float' => [1.5];
        yield 'float string' => ['1.5'];
        yield 'empty string' => [''];
        yield 'padded string' => [' 5'];
        yield 'leading zero' => ['007'];
        yield 'true' => [true];
        yield 'array' => [[10]];
        yield 'overflow' => ['99999999999999999999'];
    }

    public function testGetsInterceptors(): void
    {
        $config = new FiltersConfig([
            'interceptors' => $array = ['foo', 'bar'],
        ]);

        self::assertSame($array, $config->getInterceptors());
    }

    public function testGetsInterceptorsWhenKeyIsNotDefined(): void
    {
        $config = new FiltersConfig([]);

        self::assertSame([], $config->getInterceptors());
    }

    #[DataProvider('validLimitProvider')]
    public function testParsesNestedFiltersLimit(mixed $value, ?int $expected): void
    {
        $config = new FiltersConfig(['maxNestedFilters' => $value]);

        self::assertSame($expected, $config->getMaxNestedFilters());
    }

    public function testDefaultLimits(): void
    {
        $config = new FiltersConfig([]);

        self::assertSame(2000, $config->getMaxNestedFilters());
        self::assertSame(1024, $config->getMaxNestedKeyLength());
    }

    #[DataProvider('invalidLimitProvider')]
    public function testRejectsInvalidLimit(mixed $value): void
    {
        $config = new FiltersConfig(['maxNestedKeyLength' => $value]);

        $this->expectException(ConfigException::class);
        $config->getMaxNestedKeyLength();
    }
}
