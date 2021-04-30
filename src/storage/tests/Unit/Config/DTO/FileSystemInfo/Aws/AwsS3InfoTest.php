<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO\FileSystemInfo\Aws;

use Aws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\FileSystemInfo\Aws\AwsS3Info;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class AwsS3InfoTest extends UnitTestCase
{
    use AwsS3FsBuilderTrait;

    /**
     * @throws StorageException
     */
    public function testValidateSimple(): void
    {
        $options = [
            AwsS3Info::BUCKET_KEY => 'debugBucket',
            AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
        ];

        $fsInfo = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );

        $this->assertEquals(AwsS3V3Adapter::class, $fsInfo->getAdapterClass());

        foreach ($options as $optionKey => $optionVal) {
            $this->assertEquals($optionVal, $fsInfo->getOption($optionKey));
        }

        $this->assertNull($fsInfo->getVisibilityConverter());
    }

    /**
     * @throws StorageException
     */
    public function testValidateSimpleAsync(): void
    {
        $options = [
            AwsS3Info::BUCKET_KEY => 'debugBucket',
            AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
        ];

        $fsInfo = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AsyncAwsS3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );

        $this->assertEquals(AsyncAwsS3Adapter::class, $fsInfo->getAdapterClass());

        foreach ($options as $optionKey => $optionVal) {
            $this->assertEquals($optionVal, $fsInfo->getOption($optionKey));
        }
    }

    /**
     * @throws StorageException
     */
    public function testAdvancedUsage(): void
    {
        $options = [
            AwsS3Info::BUCKET_KEY => 'debugBucket',
            AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
            AwsS3Info::PATH_PREFIX_KEY => 'somePrefix',
            AwsS3Info::VISIBILITY_KEY => $this->getAwsS3VisibilityOption(),
        ];

        $fsInfo = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );

        $this->assertTrue($fsInfo->isAdvancedUsage());
        foreach ($options as $optionKey => $optionVal) {
            $this->assertEquals($optionVal, $fsInfo->getOption($optionKey));
        }

        $visibilityConvertor = $fsInfo->getVisibilityConverter();
        $this->assertInstanceOf(PortableVisibilityConverter::class, $visibilityConvertor);
        $this->assertSame($visibilityConvertor, $fsInfo->getVisibilityConverter());
    }

    /**
     * @throws StorageException
     */
    public function testAdvancedUsageAsync(): void
    {
        $options = [
            AwsS3Info::BUCKET_KEY => 'debugBucket',
            AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
            AwsS3Info::PATH_PREFIX_KEY => 'somePrefix',
            AwsS3Info::VISIBILITY_KEY => $this->getAwsS3VisibilityOption(),
        ];

        $advancedAwsS3Info = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AsyncAwsS3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );

        $this->assertTrue($advancedAwsS3Info->isAdvancedUsage());
        foreach ($options as $optionKey => $optionVal) {
            $this->assertEquals($optionVal, $advancedAwsS3Info->getOption($optionKey));
        }

        $visibilityConvertor = $advancedAwsS3Info->getVisibilityConverter();
        $this->assertInstanceOf(PortableVisibilityConverter::class, $visibilityConvertor);
        $this->assertSame($visibilityConvertor, $advancedAwsS3Info->getVisibilityConverter());
    }

    /**
     * @throws StorageException
     */
    public function testGetClient(): void
    {
        $options = [
            AwsS3Info::BUCKET_KEY => 'debugBucket',
            AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
        ];

        $fsInfo = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );

        $client = $fsInfo->getClient();
        $this->assertInstanceOf(S3Client::class, $client);
        $this->assertSame($client, $fsInfo->getClient());
    }

    /**
     * @dataProvider getMissedRequiredOptions
     *
     * @param string $fsName
     * @param array $options
     * @param string $exceptionMsg
     *
     * @throws StorageException
     */
    public function testValidateRequiredOptionsFailed(string $fsName, array $options, string $exceptionMsg): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage($exceptionMsg);

        new AwsS3Info(
            $fsName,
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => $options,
            ]
        );
    }

    /**
     * @throws StorageException
     */
    public function testValidateVisibilityOptionWrongTypeFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'Option `visibility` defined in wrong format for filesystem `some`, array expected'
        );

        new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => [
                    AwsS3Info::BUCKET_KEY => 'someBucket',
                    AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
                    AwsS3Info::VISIBILITY_KEY => 12,
                ],
            ]
        );
    }

    /**
     * @throws StorageException
     */
    public function testValidateVisibilityOptionWrongValueFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('`visibility` should be defined with one of values: public,private');

        new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => [
                    AwsS3Info::BUCKET_KEY => 'someBucket',
                    AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
                    AwsS3Info::VISIBILITY_KEY => [
                        AwsS3Info::CLASS_KEY => PortableVisibilityConverter::class,
                        AwsS3Info::OPTIONS_KEY => [
                            AwsS3Info::VISIBILITY_KEY => 12,
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * @throws StorageException
     */
    public function testValidateOptionalOptionsPathPrefixFailed(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'Option `path-prefix` defined in wrong format for filesystem `some`, string expected'
        );

        new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => [
                    AwsS3Info::BUCKET_KEY => 'someBucket',
                    AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
                    AwsS3Info::PATH_PREFIX_KEY => [1, 2],
                ],
            ]
        );
    }

    /**
     * @throws StorageException
     */
    public function testIsAdvancedUsage(): void
    {
        $simpleAwsS3 = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => [
                    AwsS3Info::BUCKET_KEY => 'debugBucket',
                    AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
                ],
            ]
        );

        $this->assertFalse($simpleAwsS3->isAdvancedUsage());

        $advancedAwsS3Info = new AwsS3Info(
            'some',
            [
                AwsS3Info::ADAPTER_KEY => AwsS3V3Adapter::class,
                AwsS3Info::OPTIONS_KEY => [
                    AwsS3Info::BUCKET_KEY => 'debugBucket',
                    AwsS3Info::CLIENT_KEY => $this->getAwsS3Client(),
                    AwsS3Info::PATH_PREFIX_KEY => 'somePrefix',
                ],
            ]
        );

        $this->assertTrue($advancedAwsS3Info->isAdvancedUsage());
    }

    public function getWrongUrlExpiresList(): array
    {
        $fsName = self::SERVER_NAME;
        $errorMsgPrefix = 'Url expires should be string or DateTimeInterface implemented object for filesystem ';

        return [
            [
                $fsName,
                [new \DateTime('+1 hour')],
                $errorMsgPrefix . self::SERVER_NAME,
            ],
            [
                $fsName,
                null,
                $errorMsgPrefix . self::SERVER_NAME,
            ],
            [
                'some',
                true,
                $errorMsgPrefix . 'some',
            ],
        ];
    }

    public function getMissedRequiredOptions(): array
    {
        $fsName = self::SERVER_NAME;

        return [
            [
                $fsName,
                [],
                \sprintf('Option `bucket` not detected for filesystem `%s`', $fsName),
            ],
            [
                $fsName,
                [AwsS3Info::CLIENT_KEY => 'client'],
                \sprintf('Option `bucket` not detected for filesystem `%s`', $fsName),
            ],
            [
                'some',
                [AwsS3Info::BUCKET_KEY => 'someBucket'],
                'Option `client` not detected for filesystem `some`',
            ],
        ];
    }
}
