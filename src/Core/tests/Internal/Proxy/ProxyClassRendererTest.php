<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Internal\Proxy\ProxyClassRenderer;
use Spiral\Tests\Core\Fixtures\SimpleEnum;
use Spiral\Tests\Core\Internal\Proxy\Stub\EmptyInterface;
use Spiral\Tests\Core\Internal\Proxy\Stub\StrangeInterface;

/**
 * @coversDefaultClass \Spiral\Core\Internal\Proxy\ProxyClassRenderer
 */
final class ProxyClassRendererTest extends TestCase
{
    public const STRING_CONST = 'foo';
    public const INT_CONST = 42;

    /**
     * @psalm-suppress UnusedClosureParam
     */
    public static function provideRenderParameter(): iterable
    {
        $from = static fn(\Closure $closure): \ReflectionParameter => new \ReflectionParameter($closure, 0);

        yield [$from(static fn($string) => 0), '$string'];
        yield [$from(static fn($string = '') => 0), '$string = \'\''];
        yield [$from(static fn(string $string = "\n\\'\"") => 0), "mixed \$string = '\n\\\\\\'\"'"];
        yield [$from(static fn(string $string = '123') => 0), 'mixed $string = \'123\''];
        yield [$from(static fn(string $string = self::STRING_CONST) => 0), 'mixed $string = self::STRING_CONST'];
        yield [
            $from(static fn(string $string = ProxyClassRendererTest::STRING_CONST) => 0),
            'mixed $string = \\' . self::class . '::STRING_CONST',
        ];
        yield [$from(static fn(string|int $string = self::INT_CONST) => 0), 'mixed $string = self::INT_CONST'];
        yield [$from(static fn(mixed $string = 42) => 0), 'mixed $string = 42'];
        yield [$from(static fn(int $string = 42) => 0), 'mixed $string = 42'];
        yield [$from(static fn(float $string = 42) => 0), 'mixed $string = 42.0'];
        yield [$from(static fn(?bool $string = false) => 0), 'mixed $string = false'];
        yield [$from(static fn(bool|null $string = true) => 0), 'mixed $string = true'];
        yield [$from(static fn(?object $string = null) => 0), 'mixed $string = NULL'];
        yield [$from(static fn(?iterable $string = null) => 0), 'mixed $string = NULL'];
        yield [$from(static fn(\Countable&\ArrayAccess $val) => 0), 'mixed $val'];
        yield [$from(static fn(string ...$val) => 0), 'mixed ...$val'];
        yield [$from(static fn(string|int ...$val) => 0), 'mixed ...$val'];
        yield [$from(static fn(string|int &$link) => 0), 'mixed &$link'];
        yield [$from(self::withSelf(...)), 'mixed $self = new self()'];
        yield [$from(static fn(object $link = new \stdClass()) => 0), 'mixed $link = new \stdClass()'];
        yield [
            $from(static fn(#[Proxy] float|int|\stdClass|null $string = new \stdClass(1, 2, bar: "\n'zero")) => 0),
            "mixed \$string = new \stdClass(1, 2, bar: '\n\'zero')",
        ];
        yield [
            $from(static fn(SimpleEnum $val = SimpleEnum::B) => 0),
            \sprintf('mixed $val = \%s::B', SimpleEnum::class),
        ];
    }

    public static function provideRenderMethod(): iterable
    {
        $class = new class {
            public const INT_CONST = 42;

            #[ExpectedAttribute('public function test1(...$variadic)')]
            public function test1(...$variadic) {}

            #[ExpectedAttribute('public function test2(mixed $string = self::INT_CONST): string|int')]
            public function test2(string|int $string = self::INT_CONST): string|int {}

            #[ExpectedAttribute('public function test3(mixed $obj = new \stdClass(new \stdClass(), new \stdClass()))')]
            public function test3(object $obj = new \stdClass(new \stdClass(), new \stdClass())) {}

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
     * @psalm-suppress UnusedClosureParam
     */
    public static function provideRenderParameterTypes(): iterable
    {
        $from = static fn(\Closure $closure): \ReflectionParameter => new \ReflectionParameter($closure, 0);

        yield [$from(static fn(string $string) => 0), 'string'];
        yield [$from(static fn(string|int $string) => 0), 'string|int'];
        yield [$from(static fn(mixed $string) => 0), 'mixed'];
        yield [$from(static fn(int $string) => 0), 'int'];
        yield [$from(static fn(float $string) => 0), 'float'];
        yield [$from(static fn(?bool $string) => 0), '?bool'];
        yield [$from(static fn(bool|null $string) => 0), '?bool'];
        yield [$from(static fn(object $string) => 0), 'object'];
        yield [$from(static fn(iterable $string) => 0), 'iterable'];
        yield [$from(static fn(\Countable&\ArrayAccess $val) => 0), '\Countable&\ArrayAccess'];
        yield [$from(static fn(string ...$val) => 0), 'string'];
        yield [$from(static fn(string|int ...$val) => 0), 'string|int'];
        yield [$from(static fn(string|int &$link) => 0), 'string|int'];
        yield [$from(self::withSelf(...)), '\\' . self::class];
        yield [$from(static fn(object $link) => 0), 'object'];
        yield [$from(static fn(#[Proxy] float|int|\stdClass|null $string) => 0), '\stdClass|int|float|null'];
        yield [$from(static fn(SimpleEnum $val) => 0), '\\' . SimpleEnum::class];
    }

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
     * @dataProvider provideRenderParameter
     * @covers ::renderParameter
     * @param mixed $expected
     */
    public function testRenderParameter(\ReflectionParameter $param, $expected): void
    {
        self::assertSame($expected, ProxyClassRenderer::renderParameter($param));
    }

    /**
     * @covers ::renderMethod
     * @covers ::renderParameter
     * @covers ::renderParameterTypes
     * @param mixed $expected
     */
    #[DataProvider('provideRenderMethod')]
    public function testRenderMethod(\ReflectionMethod $param, $expected): void
    {
        $rendered = ProxyClassRenderer::renderMethod($param);
        $signature = \trim(\substr($rendered, 0, \strrpos($rendered, '{') - 1));
        self::assertSame($expected, $signature);
    }

    #[DataProvider('provideRenderParameterTypes')]
    public function testRenderParameterTypes(\ReflectionParameter $param, string $expected): void
    {
        $this->assertSame(
            $expected,
            ProxyClassRenderer::renderParameterTypes($param->getType(), $param->getDeclaringClass()),
        );
    }

    private static function withSelf(self $self = new self()): void {}
}

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ExpectedAttribute
{
    public function __construct(
        public readonly string $value,
    ) {}
}
