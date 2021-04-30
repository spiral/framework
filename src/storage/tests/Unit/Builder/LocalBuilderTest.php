<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Builder;

use Spiral\Storage\Builder\Adapter\LocalBuilder;
use Spiral\Storage\Config\DTO\FileSystemInfo\Aws\AwsS3Info;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class LocalBuilderTest extends UnitTestCase
{
    use AwsS3FsBuilderTrait;

    public function testWrongServerInfoFailed(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            \sprintf('Wrong filesystem info `%s` provided for `%s`', AwsS3Info::class, LocalBuilder::class)
        );

        new LocalBuilder($this->buildAwsS3Info());
    }
}
