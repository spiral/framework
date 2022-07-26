<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Query;

final class QueryTest extends \Spiral\Tests\Filters\Model\AttributeTest
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Query('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('query', 'foo')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Query('foo');

        $this->assertSame(
            'query:foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Query();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('query', 'baz')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Query();

        $this->assertSame(
            'query:baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
