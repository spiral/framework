<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\MountException;
use Spiral\Storage\Exception\StorageException;

/**
 * tests for StorageWriterInterface methods
 */
class StorageEngineWriterTest extends StorageEngineAbstractTest
{
    private const LOCAL_FS = 'local';

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testTempFileNameThrowsException(): void
    {
        $uri = 'local://file.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('readStream')
            ->with('file.txt')
            ->willThrowException(
                UnableToReadFile::fromLocation('file.txt')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to read file from location: file.txt.');

        $storage->tempFilename($uri);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testWriteFile(): void
    {
        $fileName = 'newFile.txt';
        $fileContent = 'new File content';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('write')
            ->with($fileName, $fileContent, []);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->assertEquals(
            'local://newFile.txt',
            $storage->write(static::LOCAL_FS, $fileName, $fileContent)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testWriteFileThrowsException(): void
    {
        $fileName = 'newFile.txt';
        $fileContent = 'new File content';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('write')
            ->with($fileName, $fileContent, [])
            ->willThrowException(
                UnableToWriteFile::atLocation($fileName, 'test reason')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageMatches(
            '/^Unable to write file at location: newFile.txt. test reason/'
        );

        $storage->write(static::LOCAL_FS, $fileName, $fileContent);
    }

    /**
     * @throws StorageException
     */
    public function testWriteStream(): void
    {
        $fileName = 'newFile.txt';
        $fileContent = 'new File content';
        $config = ['visibility' => 'public'];

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('writeStream')
            ->with($fileName, $fileContent, $config);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->assertEquals(
            'local://newFile.txt',
            $storage->writeStream(static::LOCAL_FS, $fileName, $fileContent, $config)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testWriteStreamThrowsException(): void
    {
        $fileName = 'newFile.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('writeStream')
            ->willThrowException(
                UnableToWriteFile::atLocation($fileName, 'test reason')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageMatches(
            '/^Unable to write file at location: newFile.txt. test reason/'
        );

        $resource = fopen('php://memory', 'rb+');

        $storage->writeStream(
            static::LOCAL_FS,
            $fileName,
            stream_get_contents($resource)
        );

        fclose($resource);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testSetVisibility(): void
    {
        $uri = 'local://newFile.txt';
        $newVisibility = 'private';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('setVisibility')
            ->with('newFile.txt', $newVisibility);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->setVisibility($uri, $newVisibility);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testSetVisibilityThrowsException(): void
    {
        $storage = $this->buildSimpleStorageEngine();

        $uri = 'local://newFile.txt';
        $newVisibility = 'private';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('setVisibility')
            ->with('newFile.txt', $newVisibility)
            ->willThrowException(
                UnableToSetVisibility::atLocation('newFile.txt', 'test reason')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageMatches(
            '/^Unable to set visibility for file newFile.txt. test reason/'
        );

        $storage->setVisibility($uri, $newVisibility);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testDeleteFile(): void
    {
        $uri = 'local://file.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('delete')
            ->with('file.txt');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $storage->delete($uri);
    }

    /**
     * @throws StorageException
     */
    public function testDeleteFileThrowsException(): void
    {
        $uri = 'local://file.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('delete')
            ->with('file.txt')
            ->willThrowException(
                UnableToDeleteFile::atLocation('file.txt', 'test reason')
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageMatches(
            '/^Unable to delete file located at: file.txt. test reason/'
        );

        $storage->delete($uri);
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMoveFileInSameSystem(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'local';
        $targetFilePath = 'movedFile.txt';
        $config = ['visibility' => 'private'];

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('move')
            ->with('file.txt', $targetFilePath, $config);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $targetFilePath),
            $storage->move($sourceUri, $destinationFs, $targetFilePath, $config)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMoveSameFileInSameSystem(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'local';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->never())
            ->method('move');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, 'file.txt'),
            $storage->move($sourceUri, $destinationFs)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMoveFileAcrossSystems(): void
    {
        $sourceUri = 'local://file.txt';
        $filePath = 'file.txt';
        $destinationFs = 'local2';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('readStream')
            ->with($filePath);
        $localFs->expects($this->once())
            ->method('delete')
            ->with($filePath);


        $localFs2 = $this->createMock(FilesystemOperator::class);
        $localFs2->expects($this->once())
            ->method('writeStream');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);
        $this->mountStorageEngineFileSystem($storage, $destinationFs, $localFs2);

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $filePath),
            $storage->move($sourceUri, $destinationFs)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMoveFileUnknownDestinationFs(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'missed';

        $localFs = $this->createMock(FilesystemOperator::class);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(MountException::class);
        $this->expectExceptionMessage('Filesystem `missed` has not been defined');

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, 'file.txt'),
            $storage->move($sourceUri, $destinationFs)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testMoveFileInSameSystemThrowsException(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'local';
        $targetFilePath = 'movedFile.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('move')
            ->with('file.txt', $targetFilePath, [])
            ->willThrowException(
                UnableToMoveFile::fromLocationTo('file.txt', $targetFilePath)
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to move file from file.txt to movedFile.txt');

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $targetFilePath),
            $storage->move($sourceUri, $destinationFs, $targetFilePath)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testCopyFileInSameSystem(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'local';
        $targetFilePath = 'copiedFile.txt';
        $config = ['visibility' => 'private'];

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('copy')
            ->with('file.txt', $targetFilePath, $config);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $targetFilePath),
            $storage->copy($sourceUri, $destinationFs, $targetFilePath, $config)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testCopyFileAcrossSystems(): void
    {
        $sourceUri = 'local://file.txt';
        $filePath = 'file.txt';
        $destinationFs = 'local2';
        $config = ['visibility' => 'public'];

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('readStream')
            ->with($filePath);

        $localFs2 = $this->createMock(FilesystemOperator::class);
        $localFs2->expects($this->once())
            ->method('writeStream');

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);
        $this->mountStorageEngineFileSystem($storage, $destinationFs, $localFs2);

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $filePath),
            $storage->copy($sourceUri, $destinationFs, null, $config)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testCopyFileUnknownDestinationSystem(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'missed';

        $localFs = $this->createMock(FilesystemOperator::class);

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(MountException::class);
        $this->expectExceptionMessage('Filesystem `missed` has not been defined');

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, 'file.txt'),
            $storage->copy($sourceUri, $destinationFs)
        );
    }

    /**
     * @throws StorageException
     * @throws \ReflectionException
     */
    public function testCopyFileInSameSystemThrowsException(): void
    {
        $sourceUri = 'local://file.txt';
        $destinationFs = 'local';
        $targetFilePath = 'movedFile.txt';

        $localFs = $this->createMock(FilesystemOperator::class);
        $localFs->expects($this->once())
            ->method('copy')
            ->with('file.txt', $targetFilePath, [])
            ->willThrowException(
                UnableToCopyFile::fromLocationTo('file.txt', $targetFilePath)
            );

        $storage = $this->buildSimpleStorageEngine(static::LOCAL_FS, $localFs);

        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Unable to move file from file.txt to movedFile.txt');

        $this->assertEquals(
            \sprintf('%s://%s', $destinationFs, $targetFilePath),
            $storage->copy($sourceUri, $destinationFs, $targetFilePath)
        );
    }
}
