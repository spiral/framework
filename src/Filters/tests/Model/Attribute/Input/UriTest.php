<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Mockery as m;
use Psr\Http\Message\UriInterface;
use Spiral\Filters\Attribute\Input\Uri;

final class UriTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new Uri();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('uri')
            ->andReturn($uri = m::mock(UriInterface::class));

        $this->assertSame(
            $uri,
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchema(): void
    {
        $attribute = new Uri();

        $this->assertSame(
            'uri',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
