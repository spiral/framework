<?php

namespace Spiral\Tests\Core\Internal\Proxy;

use ArrayAccess;
use Countable;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Internal\Proxy\ProxyClassRenderer;
use stdClass;

/**
 * @coversDefaultClass \Spiral\Core\Internal\Proxy\ProxyClassRenderer
 */
final class ProxyClassRendererTest extends TestCase
{
    public const STRING_CONST = 'foo';
    public const INT_CONST = 42;
    // public const ENUM_CONST =

    /**
     * @psalm-suppress UnusedClosureParam
     */
    public static function provideRenderType(): iterable
    {
        $from = static fn(\Closure $closure): \ReflectionParameter => new \ReflectionParameter($closure, 0);

        yield [$from(fn(string $string = '') => 0), 'string $string = \'\''];
        yield [$from(fn(string $string = "\n\\'\"") => 0), "string \$string = '\n\\\\\\'\"'"];
        yield [$from(fn(string $string = '123') => 0), 'string $string = \'123\''];
        yield [$from(fn(string $string = self::STRING_CONST) => 0), 'string $string = self::STRING_CONST'];
        yield [
            $from(fn(string $string = ProxyClassRendererTest::STRING_CONST) => 0),
            'string $string = \\' . self::class . '::STRING_CONST',
        ];
        yield [$from(fn(string|int $string = self::INT_CONST) => 0), 'string|int $string = self::INT_CONST'];
        yield [$from(fn(int $string = 42) => 0), 'int $string = 42'];
        yield [$from(fn(float $string = 42) => 0), 'float $string = 42.0'];
        yield [$from(fn(?bool $string = false) => 0), '?bool $string = false'];
        yield [$from(fn(bool|null $string = true) => 0), '?bool $string = true'];
        yield [$from(fn(object $string = null) => 0), '?object $string = NULL'];
        yield [$from(fn(Countable&ArrayAccess $val) => 0), 'Countable&ArrayAccess $val'];
        yield [$from(fn(string ...$val) => 0), 'string ...$val'];
        yield [$from(fn(string|int ...$val) => 0), 'string|int ...$val'];
        yield [$from(fn(string|int &$link) => 0), 'string|int &$link'];
        // yield [$from(self::withSelf(...)), 'object $link = new self()'];
        // yield [$from(fn(object $link = new stdClass()) => 0), 'object $link = new stdClass()'];
    }

    private static function withSelf(self $self = new self()): void
    {
    }

    /**
     * @dataProvider provideRenderType
     * @covers ::renderParameter
     * @covers ::renderParameterTypes
     */
    public function testRenderParameter(\ReflectionParameter $param, $expected): void
    {
        self::assertSame($expected, ProxyClassRenderer::renderParameter($param));
    }
}
