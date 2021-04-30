<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit;

use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Exception\ResolveException;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Exception\UriException;
use Spiral\Storage\Parser\UriParserInterface;
use Spiral\Storage\Resolver;
use Spiral\Storage\Resolver\AdapterResolverInterface;
use Spiral\Storage\Resolver\LocalSystemResolver;
use Spiral\Storage\UriResolver;
use Spiral\Tests\Storage\Traits\AwsS3FsBuilderTrait;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;

class ResolveManagerTest extends UnitTestCase
{
    use LocalFsBuilderTrait;
    use AwsS3FsBuilderTrait;

    /**
     * @var string
     */
    private const LOCAL_SERVER_1 = 'local';

    /**
     * @var string
     */
    private const LOCAL_SERVER_2 = 'local2';

    /**
     * @var string
     */
    private const LOCAL_SERVER_ROOT_2 = '/some/specific/root/';

    /**
     * @var string
     */
    private const LOCAL_SERVER_HOST_2 = 'http://my.images.com/';

    /**
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function testGetResolverFailed(): void
    {
        $this->notice('This is an unreliable test because it invokes a non-public implementation method');

        $config = $this->buildStorageConfig([
            'local' => $this->buildLocalInfoDescription(),
        ]);

        $resolver = new UriResolver($config, $this->getUriParser());

        $missedFs = 'missedFs';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(\sprintf('Bucket `%s` was not found', $missedFs));

        $this->callNotPublicMethod($resolver, 'getResolver', [$missedFs]);
    }

    /**
     * @dataProvider getFsInfoListForResolversPrepare
     *
     * @param FileSystemInfoInterface $fsInfo
     * @param string $expectedClass
     *
     * @throws \ReflectionException
     * @throws ConfigException
     */
    public function testPrepareResolverForFileSystem(FileSystemInfoInterface $fsInfo, string $expectedClass): void
    {
        $this->notice('This is an unreliable test because it invokes a non-public implementation method');

        $config = $this->buildStorageConfig([
            'local' => $this->buildLocalInfoDescription(),
            'aws'   => $this->buildAwsS3ServerDescription(),
        ]);

        $resolver = new UriResolver($config, $this->getUriParser());

        /** @var AdapterResolverInterface $resolver */
        $resolver = $this->callNotPublicMethod($resolver, 'prepareResolverForFileSystem', [$fsInfo]);

        $this->assertInstanceOf($expectedClass, $resolver);
    }

    /**
     * @throws StorageException
     */
    public function testBuildUrlThrowException(): void
    {
        $uri = 'some:/+/someFile.txt';

        $config = $this->buildStorageConfig([
            self::LOCAL_SERVER_1 => $this->buildLocalInfoDescription(),
        ]);

        $resolver = new UriResolver($config, $this->getUriParser());

        $this->expectException(UriException::class);
        $this->expectExceptionMessage('Filesystem pathname can not be empty');

        $resolver->resolve($uri);
    }

    /**
     * @throws StorageException
     */
    public function testBuildUrlThrowableException(): void
    {
        $uri = 'local://someFile.txt';

        $exceptionMsg = 'Some unhandled exception';

        $uriParser = $this->createMock(UriParserInterface::class);
        $uriParser->expects($this->once())
            ->method('parse')
            ->willThrowException(new \Exception($exceptionMsg))
        ;

        $config = $this->buildStorageConfig([
            self::LOCAL_SERVER_1 => $this->buildLocalInfoDescription()
        ]);

        $resolver = new UriResolver($config, $uriParser);

        $this->expectException(ResolveException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $resolver->resolve($uri);
    }

    /**
     * @throws StorageException
     */
    public function testBuildUrlWrongFormatThrowsException(): void
    {
        $config = $this->buildStorageConfig([
            self::LOCAL_SERVER_1 => $this->buildLocalInfoDescription()
        ]);

        $resolver = new UriResolver($config, $this->getUriParser());

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Bucket `unknown` was not found');

        $resolver->resolve('unknown://someFile.txt');
    }

    /**
     * @return array[]
     *
     * @throws StorageException
     */
    public function getFsInfoListForResolversPrepare(): array
    {
        return [
            [$this->buildLocalInfo('localBucket'), LocalSystemResolver::class],
            [$this->buildAwsS3Info('awsBucket'), Resolver\AwsS3Resolver::class],
        ];
    }
}
