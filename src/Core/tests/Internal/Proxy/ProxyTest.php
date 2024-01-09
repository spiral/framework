<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;
use Spiral\Tests\Core\Internal\Proxy\Stub\EmptyInterface;
use Spiral\Tests\Core\Internal\Proxy\Stub\MockInterface;
use Spiral\Tests\Core\Internal\Proxy\Stub\MockInterfaceImpl;

final class ProxyTest extends TestCase
{
    public static function interfacesProvider(): iterable
    {
        yield [MockInterface::class, 'mock'];
        /** Need to set {@see Proxy::$proxyOverloads} to TRUE */
        // yield [EmptyInterface::class, 'empty'];
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testSimpleCases(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy(/*proxyOverloads: true*/)] EmptyInterface $empty,
        ) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });
    }

    public function testMagicCallsOnNonMagicProxy(): void
    {
        $root = new Container();
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        self::expectExceptionMessageMatches('/Call to undefined method/i');

        $root->invoke(static function (#[Proxy] EmptyInterface $proxy) {
            $proxy->bar(name: 'foo'); // Possible to run
        });
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testExtraArguments(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy(/*proxyOverloads: true*/)] EmptyInterface $empty,
        ) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extra('foo', 'bar', 'baz', 69));
            // With reference
            $str = 'bar';
            self::assertSame(['foo', 'foobar', 'baz'], $proxy->concat('foo', $str, 'baz'));
        });
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testVariadic(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy(/*proxyOverloads: true*/)] EmptyInterface $empty,
        ) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extraVariadic('foo', 'bar', 'baz', 69));
            self::assertSame(
                ['foo' => 'foo','zap' => 'bar', 'gas' => 69],
                $proxy->extraVariadic(foo: 'foo', zap: 'bar', gas: 69),
            );
        });
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testReference(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to pass reference using magic method __call()'
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            $str = 'bar';
            $proxy->concat('foo', $str);
            self::assertSame('foobar', $str);
        });
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testReturnReference(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to return reference using magic method __call()'
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;

            $x = 'foo';
            $y = &$proxy->same($x);
            self::assertSame($x, $y);
            $y .= 'test';
            self::assertSame($x, $y);
        });
    }

    /**
     * @dataProvider interfacesProvider
     */
    public function testReferenceVariadic(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to pass reference using magic method __call()'
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var) {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            $str1 = 'bar';
            $str2 = 'baz';
            $res = $proxy->concatMultiple('foo', $str1, $str2);
            self::assertSame('foobar', $str1);
            self::assertSame('foobaz', $str2);
            self::assertSame(['foobar', 'foobaz'], $res);
            // Named
            $str1 = 'bar';
            $str2 = 'baz';
            $res = $proxy->concatMultiple('foo', foo: $str1, bar: $str2);
            self::assertSame('foobar', $str1);
            self::assertSame('foobaz', $str2);
            self::assertSame(['foo' => 'foobar', 'bar' => 'foobaz'], $res);
        });
    }

    public function testProxyToUnionType(): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $mock = $root->invoke(static fn(#[Proxy] MockInterface|EmptyInterface $mock) => $mock);

        self::assertInstanceOf(MockInterfaceImpl::class, $mock);
    }
}
