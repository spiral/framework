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

        $this->assertTrue($bag->has('foo'));
        $this->assertTrue($bag->has('Foo'));
        $this->assertTrue($bag->has('FOO'));

        $this->assertTrue($bag->has('baz.bar'));
        $this->assertTrue($bag->has('BAZ.bar'));

        $this->assertFalse($bag->has('FOO-BAZ'));
        $this->assertTrue($bag->has('FOO-BAR'));
    }

    public function testGet(): void
    {
        $bag = new ServerBag(data: ['FOO' => 'bar', 'BAZ' => ['BAR' => 'baf'], 'FOO_BAR' => 12334]);

        $this->assertSame('bar', $bag->get('foo'));
        $this->assertSame('bar', $bag->get('Foo'));
        $this->assertSame('baf', $bag->get('baz.bar'));

        $this->assertSame(12334, $bag->get('FOO-BAR'));

        $this->assertNull($bag->get('FOO-BAZ'));
    }
}
