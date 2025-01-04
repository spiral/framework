<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute;

use Spiral\Filters\Attribute\NestedFilter;

final class NestedFilterTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsSchemaWithPrefix(): void
    {
        $attribute = new NestedFilter(
            'foo',
            'baz'
        );

        self::assertSame(['foo', 'baz'], $attribute->getSchema($this->makeProperty()));
    }

    public function testGetsSchemaWithoutPrefix(): void
    {
        $attribute = new NestedFilter(
            'foo'
        );

        self::assertSame('foo', $attribute->getSchema($this->makeProperty()));
    }
}
