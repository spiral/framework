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

        $this->assertTrue($bag->has('foo'));
        $this->assertTrue($bag->has('Foo'));
        $this->assertFalse($bag->has('FOO'));

        $this->assertTrue($bag->has('baz.bar'));
        $this->assertFalse($bag->has('BAZ.bar'));

        $this->assertFalse($bag->has('FOO-BAZ'));
        $this->assertTrue($bag->has('Foo-Bar'));
        $this->assertFalse($bag->has('FOO-BAR'));
    }

    public function testGet(): void
    {
        $bag = new HeadersBag(data: ['Foo' => 'bar', 'Baz' => ['bar' => 'baf'], 'Foo-Bar' => '12334']);

        $this->assertSame('bar', $bag->get('foo'));
        $this->assertSame('bar', $bag->get('Foo'));
        $this->assertSame('baf', $bag->get('baz.bar'));

        $this->assertSame('12334', $bag->get('Foo-Bar'));

        $this->assertNull($bag->get('FOO-BAZ'));
    }
}
