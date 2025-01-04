<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Input;

final class InputTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Input('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('input', 'foo')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Input('foo');

        self::assertSame('input:foo', $attribute->getSchema($this->makeProperty()));
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Input();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('input', 'baz')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Input();

        self::assertSame('input:baz', $attribute->getSchema($this->makeProperty()));
    }
}
