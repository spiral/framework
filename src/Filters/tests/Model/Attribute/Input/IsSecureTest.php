<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\IsSecure;

final class IsSecureTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new IsSecure();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('isSecure')
            ->andReturnTrue();

        $this->assertTrue(
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new IsSecure();

        $this->assertSame(
            'isSecure',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
