<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Request;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Request\HeadersBag;

final class HeadersBagTest extends TestCase
{
    public function testHas(): void
    {
        $bag = new HeadersBag(data: ['Foo' => 'bar', 'Baz' => ['bar' => 'baf'], 'Foo-Bar' => 12334]);

        self::assertTrue($bag->has('foo'));
        self::assertTrue($bag->has('Foo'));
        self::assertFalse($bag->has('FOO'));

        self::assertTrue($bag->has('baz.bar'));
        self::assertFalse($bag->has('BAZ.bar'));

        self::assertFalse($bag->has('FOO-BAZ'));
        self::assertTrue($bag->has('Foo-Bar'));
        self::assertFalse($bag->has('FOO-BAR'));
    }

    public function testGet(): void
    {
        $bag = new HeadersBag(data: ['Foo' => 'bar', 'Baz' => ['bar' => 'baf'], 'Foo-Bar' => '12334']);

        self::assertSame('bar', $bag->get('foo'));
        self::assertSame('bar', $bag->get('Foo'));
        self::assertSame('baf', $bag->get('baz.bar'));

        self::assertSame('12334', $bag->get('Foo-Bar'));

        self::assertNull($bag->get('FOO-BAZ'));
    }
}
