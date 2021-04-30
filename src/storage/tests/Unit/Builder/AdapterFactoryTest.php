<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Builder;

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use PHPUnit\Framework\MockObject\MockObject;
use Spiral\Storage\Builder\AdapterFactory;
use Spiral\Storage\Config\DTO\FileSystemInfo;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class AdapterFactoryTest extends UnitTestCase
{
    use LocalFsBuilderTrait;
    use AwsS3FsBuilderTrait;

    /**
     * @throws StorageException
     */
    public function testBuildSimpleLocalFs(): void
    {
        $info = $this->buildLocalInfo();

        $adapter = AdapterFactory::build($info);

        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testBuildAdvancedLocalFs(): void
    {
        $options = [
            FileSystemInfo\LocalInfo::ROOT_DIR_KEY => self::ROOT_DIR,
            FileSystemInfo\LocalInfo::HOST_KEY => self::CONFIG_HOST,
            FileSystemInfo\LocalInfo::WRITE_FLAGS_KEY => LOCK_NB,
            FileSystemInfo\LocalInfo::LINK_HANDLING_KEY => LocalFilesystemAdapter::SKIP_LINKS,
            FileSystemInfo\LocalInfo::VISIBILITY_KEY => [
                'file' => [
                    'public' => 0777,
                    'private' => 0644,
                ],
                'dir' => [
                    'public' => 0776,
                    'private' => 0444,
                ],
            ],
        ];

        $info = new FileSystemInfo\LocalInfo(
            'debugLocal',
            [
                FileSystemInfo\LocalInfo::ADAPTER_KEY => LocalFilesystemAdapter::class,
                FileSystemInfo\LocalInfo::OPTIONS_KEY => $options,
            ]
        );

        $adapter = AdapterFactory::build($info);

        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);


        $this->assertEquals(
            $options[FileSystemInfo\LocalInfo::LINK_HANDLING_KEY],
            $this->getNotPublicProperty($adapter, 'linkHandling')
        );
        $this->assertEquals(
            $options[FileSystemInfo\LocalInfo::WRITE_FLAGS_KEY],
            $this->getNotPublicProperty($adapter, 'writeFlags')
        );
        $this->assertEquals(
            PortableVisibilityConverter::fromArray($options[FileSystemInfo\LocalInfo::VISIBILITY_KEY]),
            $this->getNotPublicProperty($adapter, 'visibility')
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testBuildSimpleAwsS3Fs(): void
    {
        $fsDescription = $this->buildAwsS3ServerDescription();
        $fsInfo = new FileSystemInfo\Aws\AwsS3Info('awsS3', $fsDescription);

        $adapter = AdapterFactory::build($fsInfo);

        $this->assertInstanceOf(AwsS3V3Adapter::class, $adapter);

        $this->assertEquals(
            $fsDescription[FileSystemInfo\Aws\AwsS3Info::OPTIONS_KEY][FileSystemInfo\Aws\AwsS3Info::BUCKET_KEY],
            $this->getNotPublicProperty($adapter, 'bucket')
        );
        $this->assertSame(
            $fsInfo->getClient(),
            $this->getNotPublicProperty($adapter, 'client')
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testBuildAdvancedAwsS3Fs(): void
    {
        $options = [
            FileSystemInfo\Aws\AwsS3Info::BUCKET_KEY => 'testBucket',
            FileSystemInfo\Aws\AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
            FileSystemInfo\Aws\AwsS3Info::PATH_PREFIX_KEY => '/some/prefix/',
            FileSystemInfo\Aws\AwsS3Info::VISIBILITY_KEY => $this->getAwsS3VisibilityOption(),
        ];

        $info = new FileSystemInfo\Aws\AwsS3Info(
            'debugAwsS3',
            [
                FileSystemInfo\LocalInfo::ADAPTER_KEY => AwsS3V3Adapter::class,
                FileSystemInfo\LocalInfo::OPTIONS_KEY => $options,
            ]
        );

        $adapter = AdapterFactory::build($info);

        $this->assertInstanceOf(AwsS3V3Adapter::class, $adapter);


        $this->assertEquals(
            new PathPrefixer($options[FileSystemInfo\Aws\AwsS3Info::PATH_PREFIX_KEY]),
            $this->getNotPublicProperty($adapter, 'prefixer')
        );
        $this->assertEquals(
            $info->getVisibilityConverter(),
            $this->getNotPublicProperty($adapter, 'visibility')
        );
    }

    public function testWrongFsInfoUsage(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Adapter can\'t be built by filesystem info');

        /** @var MockObject|FileSystemInfo\FileSystemInfo $info */
        $info = $this->getMockForAbstractClass(
            FileSystemInfo\FileSystemInfo::class,
            [
                'someName',
                [
                    FileSystemInfo\FileSystemInfo::ADAPTER_KEY => LocalFilesystemAdapter::class,
                    FileSystemInfo\FileSystemInfo::OPTIONS_KEY => [],
                ],
            ]
        );

        AdapterFactory::build($info);
    }
}
