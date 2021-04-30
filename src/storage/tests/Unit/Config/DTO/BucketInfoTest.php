<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO;

use Spiral\Storage\Config\DTO\BucketInfo;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class BucketInfoTest extends UnitTestCase
{
    use LocalFsBuilderTrait;
    use AwsS3FsBuilderTrait;

    /**
     * @throws StorageException
     */
    public function testGetDirectoryOption(): void
    {
        $directory = '/files/debug/';

        $localInfo = $this->buildLocalInfo();

        $dtoNull = new BucketInfo('dBucket', $localInfo->getName());

        $this->assertNull($dtoNull->getOption(BucketInfo::DIRECTORY_KEY));

        $dto = new BucketInfo(
            'dBucket2',
            $localInfo->getName(),
            [
                'server' => $localInfo->getName(),
                'options' => [
                    'directory' => $directory,
                ],
            ]
        );

        $this->assertNull($dto->getFileSystemInfo());

        $this->assertEquals($directory, $dto->getOption(BucketInfo::DIRECTORY_KEY));

        $dto->setFileSystemInfo($localInfo);

        $this->assertSame($localInfo, $dto->getFileSystemInfo());
    }

    /**
     * @throws StorageException
     */
    public function testGetBucket(): void
    {
        $bucket = 'awsBucket1';

        $awsInfo = $this->buildAwsS3Info();

        $dtoNull = new BucketInfo('dBucket', $awsInfo->getName());

        $this->assertNull($dtoNull->getOption(BucketInfo::BUCKET_KEY));

        $dto = new BucketInfo(
            'dBucket2',
            $awsInfo->getName(),
            [
                'server' => $awsInfo->getName(),
                'options' => [
                    'bucket' => $bucket,
                ],
            ]
        );

        $this->assertNull($dto->getFileSystemInfo());

        $this->assertEquals($bucket, $dto->getOption(BucketInfo::BUCKET_KEY));
    }

    /**
     * @throws StorageException
     */
    public function testSetFileSystemInfo(): void
    {
        $localInfo = $this->buildLocalInfo();

        $dto = new BucketInfo(
            'dBucket2',
            $localInfo->getName(),
            [
                'server' => $localInfo->getName(),
                'options' => [
                    'directory' => 'someDir/',
                ],
            ]
        );

        $this->assertNull($dto->getFileSystemInfo());

        $dto->setFileSystemInfo($localInfo);

        $this->assertSame($localInfo, $dto->getFileSystemInfo());
    }
}
