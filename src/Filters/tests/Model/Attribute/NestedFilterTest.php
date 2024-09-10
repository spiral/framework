<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute;

use Spiral\Tests\Filters\Model\AttributeTestCase;
use Spiral\Filters\Attribute\NestedFilter;

final class NestedFilterTest extends AttributeTestCase
{
    public function testGetsSchemaWithPrefix(): void
    {
        $attribute = new NestedFilter(
            'foo',
            'baz'
        );

        $this->assertSame(
            ['foo', 'baz'],
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsSchemaWithoutPrefix(): void
    {
        $attribute = new NestedFilter(
            'foo'
        );

        $this->assertSame(
            'foo',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
