<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;

final class ProxyTest extends TestCase
{
    public function testSimpleCases(): void
    {
        $root = new Container();
        $root->bindSingleton(Stub\MockInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] Stub\MockInterface $proxy) {
            $proxy->bar(name: 'foo'); // Possible to run
            self::assertSame('foo', $proxy->baz('foo', 42));
            self::assertSame(123, $proxy->qux(age: 123));
            self::assertSame(69, $proxy->space(test age: 69));
        });
    }

    public function testExtraArguments(): void
    {
        $root = new Container();
        $root->bindSingleton(Stub\MockInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] Stub\MockInterface $proxy) {
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extra('foo', 'bar', 'baz', 69));
            self::assertSame(['foo', 'bar', 'baz', 69], $proxy->extraVariadic('foo', 'bar', 'baz', 69));
        });
    }

    public function testReference(): void
    {
        $root = new Container();
        $root->bindSingleton(Stub\MockInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] Stub\MockInterface $proxy) {
            $str = 'bar';
            $proxy->concat('foo', $str);
            self::assertSame('foobar', $str);
        });
    }

    public function testReferenceVariadic(): void
    {
        $root = new Container();
        $root->bindSingleton(Stub\MockInterface::class, Stub\MockInterfaceImpl::class);

        $root->invoke(static function (#[Proxy] Stub\MockInterface $proxy) {
            $str1 = 'bar';
            $str2 = 'baz';
            $res = $proxy->concatMultiple('foo', $str1, $str2);
            self::assertSame('foobar', $str1);
            self::assertSame('foobaz', $str2);
            self::assertSame(['foobar', 'foobaz'], $res);
        });
    }
}
