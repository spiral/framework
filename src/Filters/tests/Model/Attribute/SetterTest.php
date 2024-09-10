<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute;

use Spiral\Tests\Filters\Model\AttributeTestCase;
use Spiral\Filters\Attribute\Setter;

final class SetterTest extends AttributeTestCase
{
    public function testFilter(): void
    {
        $attribute = new Setter('md5');

        $this->assertSame(
            'acbd18db4cc2f85cedef654fccc4a4d8',
            $attribute->updateValue('foo')
        );
    }
}
