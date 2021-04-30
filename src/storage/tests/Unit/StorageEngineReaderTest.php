<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\StorageException;

/**
 * tests for StorageReaderInterface methods
 */
class StorageEngineReaderTest extends StorageEngineAbstractTest
{
    private const LOCAL_FS = 'local';

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testFileExists(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('fileExists')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->fileExists('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     * @throws \Spiral\Storage\Exception\ConfigException
     */
    public function testFileExistsThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('fileExists')
            ->willThrowException(
                UnableToCheckFileExistence::forLocation('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to check file existence for: file.txt');

        $storage->fileExists('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testRead(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('read')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->read('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testReadThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('read')
            ->willThrowException(
                UnableToReadFile::fromLocation('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to read file from location: file.txt.');

        $storage->read('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testReadStream(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('readStream')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->readStream('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testReadStreamThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('readStream')
            ->willThrowException(
                UnableToReadFile::fromLocation('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to read file from location: file.txt.');

        $storage->readStream('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testLastModified(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('lastModified')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->lastModified('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testLastModifiedThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('lastModified')
            ->willThrowException(
                UnableToRetrieveMetadata::lastModified('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to retrieve the last_modified for file at location: file.txt.');

        $storage->lastModified('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testFileSize(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('fileSize')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->fileSize('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testFileSizeThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('fileSize')
            ->willThrowException(
                UnableToRetrieveMetadata::fileSize('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to retrieve the file_size for file at location: file.txt.');

        $storage->fileSize('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMimeType(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('mimeType')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->mimeType('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMimeTypeThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('mimeType')
            ->willThrowException(
                UnableToRetrieveMetadata::mimeType('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to retrieve the mime_type for file at location: file.txt.');

        $storage->mimeType('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testVisibility(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('visibility')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->visibility('local://file.txt');
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testVisibilityThrowsException(): void
    {
        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('visibility')
            ->willThrowException(
                UnableToRetrieveMetadata::visibility('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to retrieve the visibility for file at location: file.txt.');

        $storage->visibility('local://file.txt');
    }
}
