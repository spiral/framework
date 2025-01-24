<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Config;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Scope;
use Spiral\Tests\Core\Fixtures\ScopeEnum;
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

    public static function invalidDeprecationProxyArgsDataProvider(): \Traversable
    {
        yield [null, '4.0'];
        yield ['foo', null];
        yield [null, null];
    }

    #[DataProvider('interfacesProvider')]
    public function testSimpleCases(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy/*proxyOverloads: true*/] EmptyInterface $empty,
        ) use ($var): void {
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

        $root->invoke(static function (#[Proxy] EmptyInterface $proxy): void {
            $proxy->bar(name: 'foo'); // Possible to run
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testExtraArguments(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy/*proxyOverloads: true*/] EmptyInterface $empty,
        ) use ($var): void {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extra('foo', 'bar', 'baz', 69));
            // With reference
            $str = 'bar';
            self::assertSame(['foo', 'foobar', 'baz'], $proxy->concat('foo', $str, 'baz'));
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testVariadic(string $interface, string $var): void
    {
        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (
            #[Proxy] MockInterface $mock,
            #[Proxy/*proxyOverloads: true*/] EmptyInterface $empty,
        ) use ($var): void {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extraVariadic('foo', 'bar', 'baz', 69));
            self::assertSame(
                ['foo' => 'foo','zap' => 'bar', 'gas' => 69],
                $proxy->extraVariadic(foo: 'foo', zap: 'bar', gas: 69),
            );
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testReference(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to pass reference using magic method __call()',
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var): void {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;
            $str = 'bar';
            $proxy->concat('foo', $str);
            self::assertSame('foobar', $str);
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testReturnReference(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to return reference using magic method __call()',
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var): void {
            /** @var MockInterfaceImpl $proxy */
            $proxy = $$var;

            $x = 'foo';
            $y = &$proxy->same($x);
            self::assertSame($x, $y);
            $y .= 'test';
            self::assertSame($x, $y);
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testReferenceVariadic(string $interface, string $var): void
    {
        $interface === EmptyInterface::class && self::markTestSkipped(
            'Impossible to pass reference using magic method __call()',
        );

        $root = new Container();
        $root->bindSingleton(MockInterface::class, Stub\MockInterfaceImpl::class);
        $root->bindSingleton(EmptyInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] MockInterface $mock, #[Proxy] EmptyInterface $empty) use ($var): void {
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

    #[DataProvider('interfacesProvider')]
    public function testProxyConfig(string $interface): void
    {
        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\Proxy($interface, true));

        $proxy = $root->get($interface);
        $this->assertInstanceOf($interface, $proxy);
        $this->assertNotInstanceOf(MockInterfaceImpl::class, $proxy);

        $root->runScope(new Scope('foo'), static function (Container $container) use ($interface, $proxy): void {
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));

            $real = $container->get($interface);
            self::assertInstanceOf(MockInterfaceImpl::class, $real);

            $real->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $real->baz('foo', 42));
            self::assertSame(123, $real->qux(age: 123));
            self::assertSame(69, $real->space(test age: 69));
        });
    }

    #[DataProvider('interfacesProvider')]
    public function testProxyConfigOutOfProxyException(string $interface): void
    {
        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\Proxy($interface, true));

        $this->assertInstanceOf($interface, $root->get($interface));
        $proxy = $root->get($interface);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Proxy is out of scope.');
        $proxy->bar(name: 'foo'); // Impossible to run
    }

    #[DataProvider('interfacesProvider')]
    #[WithoutErrorHandler]
    public function testDeprecationProxyConfig(string $interface): void
    {
        \set_error_handler(static function (int $errno, string $error) use ($interface): void {
            self::assertSame(
                \sprintf('Using `%s` outside of the `foo` scope is deprecated and will be ' .
                    'impossible in version 4.0.', $interface),
                $error,
            );
        });

        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\DeprecationProxy($interface, true, 'foo', '4.0'));

        $proxy = $root->get($interface);
        $this->assertInstanceOf($interface, $proxy);

        $root->runScope(new Scope('foo'), static function () use ($proxy): void {
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });

        \restore_error_handler();
    }

    #[DataProvider('interfacesProvider')]
    #[WithoutErrorHandler]
    public function testDeprecationProxyConfigCustomMessage(string $interface): void
    {
        \set_error_handler(static function (int $errno, string $error) use ($interface): void {
            self::assertSame(\sprintf('Using `%s` impossible', $interface), $error);
        });

        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\DeprecationProxy(
            interface: $interface,
            message: \sprintf('Using `%s` impossible', $interface),
        ));

        $proxy = $root->get($interface);
        $this->assertInstanceOf($interface, $proxy);

        $root->runScope(new Scope('foo'), static function () use ($proxy): void {
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });

        \restore_error_handler();
    }

    #[DataProvider('interfacesProvider')]
    #[WithoutErrorHandler]
    public function testDeprecationProxyConfigWithEnumScope(string $interface): void
    {
        \set_error_handler(static function (int $errno, string $error) use ($interface): void {
            self::assertSame(
                \sprintf('Using `%s` outside of the `a` scope is deprecated and will be ' .
                    'impossible in version 4.0.', $interface),
                $error,
            );
        });

        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\DeprecationProxy($interface, true, ScopeEnum::A, '4.0'));

        $proxy = $root->get($interface);
        $this->assertInstanceOf($interface, $proxy);

        $root->runScope(new Scope('foo'), static function () use ($proxy): void {
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });

        \restore_error_handler();
    }

    #[DataProvider('interfacesProvider')]
    #[WithoutErrorHandler]
    public function testDeprecationProxyConfigDontThrowIfNotConstructed(string $interface): void
    {
        \set_error_handler(static function (int $errno, string $error) use ($interface): void {
            self::fail('Unexpected error: ' . $error);
        });

        $root = new Container();
        $root->getBinder('foo')->bindSingleton($interface, Stub\MockInterfaceImpl::class);
        $root->bindSingleton($interface, new Config\DeprecationProxy($interface, true, 'foo', '4.0'));

        $root->runScope(new Scope('foo'), static function (Container $container) use ($interface): void {
            $proxy = $container->get($interface);

            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });

        \restore_error_handler();
    }

    #[DataProvider('invalidDeprecationProxyArgsDataProvider')]
    public function testDeprecationProxyConfigArgsRequiredException(string|null $scope, string|null $version): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Scope and version or custom message must be provided.');

        new Config\DeprecationProxy(interface: EmptyInterface::class, scope: $scope, version: $version);
    }

    public function testProxyConfigToString(): void
    {
        $proxy = new Config\Proxy(EmptyInterface::class);

        $this->assertSame(\sprintf('Proxy to `%s`', EmptyInterface::class), (string) $proxy);
    }

    public function testProxyConfigNotInterfaceException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Interface `%s` does not exist.', \stdClass::class));

        new Config\Proxy(\stdClass::class);
    }
}
