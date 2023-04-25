<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Attribute\Input;

use Mockery as m;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Filters\Attribute\Input\File;

final class FileTest extends \Spiral\Tests\Filters\Model\AttributeTestCase
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new File('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('file', 'foo')
            ->andReturn($file = m::mock(UploadedFileInterface::class));

        $this->assertSame(
            $file,
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new File('foo');

        $this->assertSame(
            'file:foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new File();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('file', 'baz')
            ->andReturn($file = m::mock(UploadedFileInterface::class));

        $this->assertSame(
            $file,
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new File();

        $this->assertSame(
            'file:baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
