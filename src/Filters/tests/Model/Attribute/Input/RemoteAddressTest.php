<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Spiral\Filters\Attribute\Input\RemoteAddress;

final class RemoteAddressTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValue(): void
    {
        $attribute = new RemoteAddress();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('remoteAddress')
            ->andReturn('bar');

        self::assertSame('bar', $attribute->getValue($this->input, $this->makeProperty()));
    }

    public function testGetsSchema(): void
    {
        $attribute = new RemoteAddress();

        self::assertSame('remoteAddress', $attribute->getSchema($this->makeProperty()));
    }
}
