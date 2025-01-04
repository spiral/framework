<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Request;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Request\ServerBag;

final class ServerBagTest extends TestCase
{
    public function testHas(): void
    {
        $bag = new ServerBag(data: ['FOO' => 'bar', 'BAZ' => ['BAR' => 'baf'], 'FOO_BAR' => 12334]);

        self::assertTrue($bag->has('foo'));
        self::assertTrue($bag->has('Foo'));
        self::assertTrue($bag->has('FOO'));

        self::assertTrue($bag->has('baz.bar'));
        self::assertTrue($bag->has('BAZ.bar'));

        self::assertFalse($bag->has('FOO-BAZ'));
        self::assertTrue($bag->has('FOO-BAR'));
    }

    public function testGet(): void
    {
        $bag = new ServerBag(data: ['FOO' => 'bar', 'BAZ' => ['BAR' => 'baf'], 'FOO_BAR' => 12334]);

        self::assertSame('bar', $bag->get('foo'));
        self::assertSame('bar', $bag->get('Foo'));
        self::assertSame('baf', $bag->get('baz.bar'));

        self::assertSame(12334, $bag->get('FOO-BAR'));

        self::assertNull($bag->get('FOO-BAZ'));
    }
}
