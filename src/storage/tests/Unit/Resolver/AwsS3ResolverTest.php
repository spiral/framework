<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Resolver;

use Aws\CommandInterface;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Psr\Http\Message\RequestInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo\Aws\AwsS3Info;
use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Resolver\AwsS3Resolver;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class AwsS3ResolverTest extends UnitTestCase
{
    use LocalFsBuilderTrait;
    use AwsS3FsBuilderTrait;

    /**
     * @throws StorageException
     */
    public function testWrongFsInfo(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Wrong filesystem info (`%s`) for resolver `%s`',
                LocalInfo::class,
                AwsS3Resolver::class
            )
        );

        $localServer = 'local';
        $awsServer = 'aws';

        new AwsS3Resolver(
            $this->getUriParser(),
            $this->buildStorageConfig(
                [
                    $localServer => $this->buildLocalInfoDescription(),
                    $awsServer => $this->buildAwsS3ServerDescription(),
                ]
            ),
            $this->buildBucketNameByServer($localServer)
        );
    }

    /**
     * @throws StorageException
     */
    public function testBuildUrl(): void
    {
        $serverName = 'aws';
        $bucketName = 'awsBucket';

        $uri = 'http://some-host.com/somefile.txt';

        $commandMock = $this->createMock(CommandInterface::class);

        $requestMock = $this->createMock(RequestInterface::class);

        $requestMock->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri);

        $s3Client = $this->createMock(S3Client::class);

        $s3Client->expects($this->exactly(2))
            ->method('createPresignedRequest')
            ->willReturn($requestMock);

        $s3Client->expects($this->exactly(2))
            ->method('getCommand')
            ->willReturn($commandMock);

        $serverDescription = [
            AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
            AwsS3Info::OPTIONS_KEY => [
                AwsS3Info::BUCKET_KEY => 'debugBucket',
                AwsS3Info::CLIENT_KEY => $s3Client,
            ],
        ];

        $resolver = new AwsS3Resolver(
            $this->getUriParser(),
            $this->buildStorageConfig(
                [$serverName => $serverDescription],
                [$bucketName => $this->buildServerBucketInfoDesc($serverName)]
            ),
            $bucketName
        );

        $this->assertEquals($uri, $resolver->buildUrl('somefile.txt'));

        $this->assertEquals($uri, $resolver->buildUrl('somefile.txt', [AwsS3Resolver::EXPIRES_OPTION => '+1hour']));
    }
}
