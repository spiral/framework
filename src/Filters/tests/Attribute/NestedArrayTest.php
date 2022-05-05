<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Attribute;

use Mockery as m;
use Spiral\Filters\Attribute\Input\Input;
use Spiral\Filters\Attribute\NestedArray;

final class NestedArrayTest extends \Spiral\Tests\Filters\AttributeTest
{
    public function testGetsValue(): void
    {
        $attribute = new NestedArray(
            'foo',
            $input = m::mock(Input::class),
            'baz'
        );

        $property = $this->makeProperty();

        $input
            ->shouldReceive('getValue')
            ->once()
            ->with($this->input, $property)
            ->andReturn('bak');

        $this->assertSame(
            'bak',
            $attribute->getValue($this->input, $property)
        );
    }

    public function testGetsSchemaWithPrefix(): void
    {
        $attribute = new NestedArray(
            'foo',
            m::mock(Input::class),
            'baz'
        );

        $this->assertSame(
            ['foo', 'baz'],
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsSchemaWithoutPrefix(): void
    {
        $attribute = new NestedArray(
            'foo',
            m::mock(Input::class)
        );

        $this->assertSame(
            ['foo'],
            $attribute->getSchema($this->makeProperty())
        );
    }
}
