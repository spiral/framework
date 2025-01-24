<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute;

use Mockery as m;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Attribute\NestedArray;

final class NestedArrayTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new NestedArray(
            'foo',
            $input = m::mock(AbstractInput::class),
            'baz',
        );

        $property = $this->makeProperty();

        $input
            ->shouldReceive('getValue')
            ->once()
            ->with($this->input, $property)
            ->andReturn('bak');

        self::assertSame('bak', $attribute->getValue($this->input, $property));
    }

    public function testGetsSchemaWithPrefix(): void
    {
        $attribute = new NestedArray(
            'foo',
            m::mock(AbstractInput::class),
            'baz',
        );

        self::assertSame(['foo', 'baz'], $attribute->getSchema($this->makeProperty()));
    }

    public function testGetsSchemaWithoutPrefix(): void
    {
        $attribute = new NestedArray(
            'foo',
            m::mock(AbstractInput::class),
        );

        self::assertSame(['foo'], $attribute->getSchema($this->makeProperty()));
    }
}
