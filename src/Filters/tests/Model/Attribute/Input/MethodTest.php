<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Method;

final class MethodTest extends \Spiral\Tests\Filters\Model\AttributeTest
{
    public function testGetsValue(): void
    {
        $attribute = new Method();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('method')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new Method();

        $this->assertSame(
            'method',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
