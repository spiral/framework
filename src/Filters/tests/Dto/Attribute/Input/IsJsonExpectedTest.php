<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Dto\Attribute\Input;

use Spiral\Filters\Attribute\Input\IsJsonExpected;

final class IsJsonExpectedTest extends \Spiral\Tests\Filters\Dto\AttributeTest
{
    public function testGetsValue(): void
    {
        $attribute = new IsJsonExpected();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('isJsonExpected')
            ->andReturnTrue();

        $this->assertTrue(
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new IsJsonExpected();

        $this->assertSame(
            'isJsonExpected',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
