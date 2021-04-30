<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO\FileSystemInfo\Aws;

use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\FileSystemInfo\Aws\AwsVisibilityConverter;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class AwsVisibilityConverterTest extends UnitTestCase
{
    /**
     * @throws StorageException
     */
    public function testConstructor(): void
    {
        $info = new AwsVisibilityConverter(
            [
                AwsVisibilityConverter::CLASS_KEY => PortableVisibilityConverter::class,
                AwsVisibilityConverter::OPTIONS_KEY => [
                    AwsVisibilityConverter::VISIBILITY_KEY => Visibility::PUBLIC,
                ]
            ]
        );

        $this->assertEquals(PortableVisibilityConverter::class, $info->getClass());
        $this->assertEquals(Visibility::PUBLIC, $info->getOption(AwsVisibilityConverter::VISIBILITY_KEY));
    }

    /**
     * @throws StorageException
     */
    public function testGetConverter(): void
    {
        $info = new AwsVisibilityConverter(
            [
                AwsVisibilityConverter::CLASS_KEY => PortableVisibilityConverter::class,
                AwsVisibilityConverter::OPTIONS_KEY => [
                    AwsVisibilityConverter::VISIBILITY_KEY => Visibility::PUBLIC,
                ],
            ]
        );

        $converter = $info->getConverter();

        $this->assertInstanceOf(PortableVisibilityConverter::class, $converter);
        $this->assertSame($converter, $info->getConverter());
    }

    /**
     * @throws StorageException
     */
    public function testConstructorNoClassFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Aws visibility converter must be described with class');

        new AwsVisibilityConverter(
            [
                AwsVisibilityConverter::OPTIONS_KEY => [
                    AwsVisibilityConverter::VISIBILITY_KEY => Visibility::PUBLIC,
                ],
            ]
        );
    }

    /**
     * @throws StorageException
     */
    public function testConstructorNoOptionsFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Aws visibility converter must be described with options list');

        new AwsVisibilityConverter(
            [AwsVisibilityConverter::CLASS_KEY => PortableVisibilityConverter::class]
        );
    }

    /**
     * @throws StorageException
     */
    public function testConstructorNoVisibilityOptionFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('`visibility` option should be defined for Aws visibility converter');

        new AwsVisibilityConverter(
            [
                AwsVisibilityConverter::CLASS_KEY => PortableVisibilityConverter::class,
                AwsVisibilityConverter::OPTIONS_KEY => ['some' => 'option'],
            ]
        );
    }
}
