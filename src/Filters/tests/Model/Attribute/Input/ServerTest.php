<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Server;

final class ServerTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Server('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('server', 'foo')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Server('foo');

        self::assertSame('server:foo', $attribute->getSchema($this->makeProperty()));
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Server();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('server', 'baz')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Server();

        self::assertSame('server:baz', $attribute->getSchema($this->makeProperty()));
    }
}
