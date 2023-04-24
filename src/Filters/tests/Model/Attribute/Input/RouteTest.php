<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Route;

final class RouteTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Route('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('attribute', 'matches.foo')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Route('foo');

        $this->assertSame(
            'attribute:matches.foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Route();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('attribute', 'matches.baz')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Route();

        $this->assertSame(
            'attribute:matches.baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
