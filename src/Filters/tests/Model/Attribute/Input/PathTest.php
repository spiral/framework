<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\Path;

final class PathTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new Path();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('path')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchema(): void
    {
        $attribute = new Path();

        self::assertSame('path', $attribute->getSchema($this->makeProperty()));
    }
}
