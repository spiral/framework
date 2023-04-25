<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Cookie;

final class CookieTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Cookie('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('cookie', 'foo')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Cookie('foo');

        $this->assertSame(
            'cookie:foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Cookie();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('cookie', 'baz')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Cookie();

        $this->assertSame(
            'cookie:baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
