<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\BearerToken;

final class BearerTokenTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new BearerToken();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('bearerToken')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new BearerToken();

        $this->assertSame(
            'bearerToken',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
