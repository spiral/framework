<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy;

use ArrayAccess;
use Countable;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Internal\Proxy\ProxyClassRenderer;
use Spiral\Tests\Core\Fixtures\SimpleEnum;
use Spiral\Tests\Core\Internal\Proxy\Stub\EmptyInterface;
use Spiral\Tests\Core\Internal\Proxy\Stub\StrangeInterface;
use stdClass;

/**
 * @coversDefaultClass \Spiral\Core\Internal\Proxy\ProxyClassRenderer
 */
final class ProxyClassRendererTest extends TestCase
{
    public const STRING_CONST = 'foo';
    public const INT_CONST = 42;

    public function testInterfaceWithConstructor(): void
    {
        self::expectExceptionMessage('Constructor is not allowed in a proxy.');

        ProxyClassRenderer::renderClass(
            new \ReflectionClass(StrangeInterface::class),
            'StrangeImpl',
        );
    }

    public function testRenderClassInGlobalNamespace(): void
    {
        $result = ProxyClassRenderer::renderClass(
            new \ReflectionClass(EmptyInterface::class),
            'TestImpl',
        );
        self::assertStringNotContainsString('namespace', $result);
    }

    /**
     * @psalm-suppress UnusedClosureParam
     */
    public static function provideRenderParameter(): iterable
    {
        $from = static fn(\Closure $closure): \ReflectionParameter => new \ReflectionParameter($closure, 0);

        yield [$from(fn($string) => 0), '$string'];
        yield [$from(fn($string = '') => 0), '$string = \'\''];
        yield [$from(fn(string $string = "\n\\'\"") => 0), "string \$string = '\n\\\\\\'\"'"];
        yield [$from(fn(string $string = '123') => 0), 'string $string = \'123\''];
        yield [$from(fn(string $string = self::STRING_CONST) => 0), 'string $string = self::STRING_CONST'];
        yield [
            $from(fn(string $string = ProxyClassRendererTest::STRING_CONST) => 0),
            'string $string = \\' . self::class . '::STRING_CONST',
        ];
        yield [$from(fn(string|int $string = self::INT_CONST) => 0), 'string|int $string = self::INT_CONST'];
        yield [$from(fn(mixed $string = 42) => 0), 'mixed $string = 42'];
        yield [$from(fn(int $string = 42) => 0), 'int $string = 42'];
        yield [$from(fn(float $string = 42) => 0), 'float $string = 42.0'];
        yield [$from(fn(?bool $string = false) => 0), '?bool $string = false'];
        yield [$from(fn(bool|null $string = true) => 0), '?bool $string = true'];
        yield [$from(fn(object $string = null) => 0), '?object $string = NULL'];
        yield [$from(fn(iterable $string = null) => 0), '?iterable $string = NULL'];
        yield [$from(fn(Countable&ArrayAccess $val) => 0), '\Countable&\ArrayAccess $val'];
        yield [$from(fn(string ...$val) => 0), 'string ...$val'];
        yield [$from(fn(string|int ...$val) => 0), 'string|int ...$val'];
        yield [$from(fn(string|int &$link) => 0), 'string|int &$link'];
        yield [$from(self::withSelf(...)), \sprintf('\%s $self = new self()', self::class)];
        yield [$from(fn(object $link = new \stdClass()) => 0), 'object $link = new \stdClass()'];
        yield [
            $from(fn(#[Proxy] float|int|\stdClass|null $string = new \stdClass(1, 2, bar: "\n'zero")) => 0),
            "\stdClass|int|float|null \$string = new \stdClass(1, 2, bar: '\n\'zero')",
        ];
        yield [
            $from(fn(SimpleEnum $val = SimpleEnum::B) => 0),
            \sprintf('\%s $val = \%s::B', SimpleEnum::class, SimpleEnum::class),
        ];
    }

    /**
     * @dataProvider provideRenderParameter
     * @covers ::renderParameter
     * @covers ::renderParameterTypes
     */
    public function testRenderParameter(\ReflectionParameter $param, $expected): void
    {
        self::assertSame($expected, ProxyClassRenderer::renderParameter($param));
    }

    public static function provideRenderMethod(): iterable
    {
        $class = new class {
            public const INT_CONST = 42;

            #[ExpectedAttribute('public function test1(...$variadic)')]
            public function test1(...$variadic) {}
            #[ExpectedAttribute('public function test2(string|int $string = self::INT_CONST): string|int')]
            public function test2(string|int $string = self::INT_CONST): string|int {}
            #[ExpectedAttribute('public function test3(object $obj = new \stdClass(new \stdClass(), new \stdClass()))')]
            public function test3(object $obj = new stdClass(new stdClass(), new stdClass())) {}
            #[ExpectedAttribute('public function test4(): \\' . ProxyClassRendererTest::class)]
            public function test4(): ProxyClassRendererTest {}
            #[ExpectedAttribute('public function &test5(): string')]
            public function &test5(): string {}
        };

        foreach ((new \ReflectionClass($class))->getMethods() as $method) {
            $expected = $method->getAttributes(ExpectedAttribute::class)[0]->newInstance();

            yield [$method, $expected->value];
        }
    }

    /**
     * @dataProvider provideRenderMethod
     * @covers ::renderMethod
     * @covers ::renderParameter
     * @covers ::renderParameterTypes
     */
    public function testRenderMethod(\ReflectionMethod $param, $expected): void
    {
        $rendered = ProxyClassRenderer::renderMethod($param);
        $signature = \trim(\substr($rendered, 0, \strrpos($rendered, '{') - 1));
        self::assertSame($expected, $signature);
    }

    private static function withSelf(self $self = new self()): void
    {
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ExpectedAttribute
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
