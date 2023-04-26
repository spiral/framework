<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Data;

final class DataTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Data('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('data', 'foo')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Data('foo');

        $this->assertSame(
            'data:foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Data();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('data', 'baz')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Data();

        $this->assertSame(
            'data:baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
