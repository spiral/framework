<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Builder;

use Spiral\Storage\Builder\Adapter\AwsS3Builder;
use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class AwsS3BuilderTest extends UnitTestCase
{
    use LocalFsBuilderTrait;

    public function testWrongFsInfoFailed(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            \sprintf('Wrong filesystem info `%s` provided for `%s`', LocalInfo::class, AwsS3Builder::class)
        );

        new AwsS3Builder($this->buildLocalInfo());
    }
}
