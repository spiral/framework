<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Dto\Attribute\Input;

use Spiral\Filters\Attribute\Input\IsAjax;

final class IsAjaxTest extends \Spiral\Tests\Filters\Dto\AttributeTest
{
    public function testGetsValue(): void
    {
        $attribute = new IsAjax();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('isAjax')
            ->andReturnTrue();

        $this->assertTrue(
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new IsAjax();

        $this->assertSame(
            'isAjax',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
