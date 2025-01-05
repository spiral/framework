<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute;

use Spiral\Filters\Attribute\Setter;

final class SetterTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testFilter(): void
    {
        $attribute = new Setter('md5');

        self::assertSame('acbd18db4cc2f85cedef654fccc4a4d8', $attribute->updateValue('foo'));
    }
}
