<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Post;

final class PostTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Post('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('post', 'foo')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Post('foo');

        self::assertSame('post:foo', $attribute->getSchema($this->makeProperty()));
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Post();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('post', 'baz')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Post();

        self::assertSame('post:baz', $attribute->getSchema($this->makeProperty()));
    }
}
