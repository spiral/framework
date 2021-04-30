<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Spiral\Storage\Builder\AdapterFactory;
use Spiral\Storage\Config\ConfigInterface;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\MountException;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Exception\UriException;
use Spiral\Storage\Parser\Uri;
use Spiral\Storage\Parser\UriParserInterface;

class Storage implements StorageInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var UriParserInterface
     */
    protected $uriParser;

    /**
     * @var array<string, FilesystemOperator>
     */
    protected $fileSystems = [];

    /**
     * @param ConfigInterface $config
     * @param UriParserInterface $uriParser
     * @throws StorageException
     */
    public function __construct(ConfigInterface $config, UriParserInterface $uriParser)
    {
        $this->config = $config;
        $this->uriParser = $uriParser;

        foreach ($config->getBucketsKeys() as $fs) {
            $this->mountFilesystem($fs, new Filesystem(
                AdapterFactory::build($this->config->buildFileSystemInfo($fs))
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function getFileSystem(string $key): FilesystemOperator
    {
        if (!$this->isFileSystemExists($key)) {
            throw new MountException(\sprintf('Filesystem `%s` has not been defined', $key));
        }

        return $this->fileSystems[$key];
    }

    /**
     * @inheritDoc
     */
    public function getFileSystemsNames(): array
    {
        return \array_keys($this->fileSystems);
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $uri): bool
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->fileExists($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(string $uri): string
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->read($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $uri)
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->readStream($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $uri): int
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->lastModified($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $uri): int
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->fileSize($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $uri): string
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->mimeType($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function visibility(string $uri): string
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            return $filesystem->visibility($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function tempFilename(string $uri = null): string
    {
        try {
            $prefix = 'tmpStorageFile_';

            if ($uri !== null) {
                /** @var FilesystemOperator $filesystem */
                [$filesystem, $path] = $this->determineFilesystemAndPath($uri);
                $content = $filesystem->readStream($path);
                $prefix = basename($uri) . '_';
            }

            $filePath = tempnam($this->config->getTmpDir(), $prefix);

            if (isset($content)) {
                file_put_contents($filePath, $content);
            }

            return $filePath;
        } catch (\Throwable $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function write(string $fileSystem, string $filePath, string $content, array $config = []): string
    {
        $uri = (string)Uri::create($fileSystem, $filePath);

        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            $filesystem->write($path, $content, $config);

            return $uri;
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function writeStream(string $fileSystem, string $filePath, $content, array $config = []): string
    {
        $uri = (string)Uri::create($fileSystem, $filePath);

        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            $filesystem->writeStream($path, $content, $config);

            return $uri;
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function setVisibility(string $uri, string $visibility): void
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            $filesystem->setVisibility($path, $visibility);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function move(
        string $sourceUri,
        string $destinationFileSystem,
        ?string $targetFilePath = null,
        array $config = []
    ): string {
        /** @var FilesystemOperator $sourceFilesystem */
        [$sourceFilesystem, $sourcePath] = $this->determineFilesystemAndPath($sourceUri);

        $destinationFilesystem = $this->getFileSystem($destinationFileSystem);

        try {
            $targetFilePath = $targetFilePath ?: $sourcePath;

            $sourceFilesystem === $destinationFilesystem
                ? $this->moveInTheSameFilesystem($sourceFilesystem, $sourcePath, $targetFilePath, $config)
                : $this->moveAcrossFilesystems($sourceUri, $destinationFileSystem, $targetFilePath, $config);

            return (string)Uri::create($destinationFileSystem, $targetFilePath);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function copy(
        string $sourceUri,
        string $destinationFileSystem,
        ?string $targetFilePath = null,
        array $config = []
    ): string {
        /** @var FilesystemOperator $sourceFilesystem */
        [$sourceFilesystem, $sourcePath] = $this->determineFilesystemAndPath($sourceUri);

        $destinationFilesystem = $this->getFileSystem($destinationFileSystem);

        try {
            $targetFilePath = $targetFilePath ?: $sourcePath;

            $sourceFilesystem === $destinationFilesystem
                ? $this->copyInSameFilesystem($sourceFilesystem, $sourcePath, $targetFilePath, $config)
                : $this->copyAcrossFilesystem(
                    $config['visibility'] ?? null,
                    $sourceFilesystem,
                    $sourcePath,
                    $destinationFilesystem,
                    $targetFilePath,
                    $config
                );

            return (string)Uri::create($destinationFileSystem, $targetFilePath);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uri): void
    {
        /** @var FilesystemOperator $filesystem */
        [$filesystem, $path] = $this->determineFilesystemAndPath($uri);

        try {
            $filesystem->delete($path);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Mount new filesystem in collection
     * Key should be unique
     *
     * @param string $key
     * @param FilesystemOperator $filesystem
     *
     * @throws MountException
     */
    protected function mountFilesystem(string $key, FilesystemOperator $filesystem): void
    {
        if ($this->isFileSystemExists($key)) {
            throw new MountException(\sprintf('Filesystem %s is already mounted', $key));
        }

        $this->fileSystems[$key] = $filesystem;
    }

    /**
     * Check if filesystem was mounted
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isFileSystemExists(string $key): bool
    {
        return \array_key_exists($key, $this->fileSystems);
    }

    /**
     * Identify used filesystem key and filepath by provided uri
     *
     * @param string $uri
     *
     * @return array { 0: FilesystemOperator, 1: string }
     *
     * @throws MountException
     * @throws UriException
     */
    protected function determineFilesystemAndPath(string $uri): array
    {
        $uriStructure = $this->uriParser->parse($uri);

        return [
            $this->getFileSystem($uriStructure->getFileSystem()),
            $uriStructure->getPath()
        ];
    }

    /**
     * Copy file in one filesystem
     *
     * @param FilesystemOperator $sourceFilesystem
     * @param string $sourcePath
     * @param string $destinationPath
     * @param array $config
     *
     * @throws FilesystemException
     */
    protected function copyInSameFilesystem(
        FilesystemOperator $sourceFilesystem,
        string $sourcePath,
        string $destinationPath,
        array $config = []
    ): void {
        $sourceFilesystem->copy($sourcePath, $destinationPath, $config);
    }

    /**
     * Copy file across different filesystems
     *
     * @param string|null $visibility
     * @param FilesystemOperator $sourceFilesystem
     * @param string $sourcePath
     * @param FilesystemOperator $destinationFilesystem
     * @param string $destinationPath
     * @param array $config
     *
     * @throws FilesystemException
     */
    protected function copyAcrossFilesystem(
        ?string $visibility,
        FilesystemOperator $sourceFilesystem,
        string $sourcePath,
        FilesystemOperator $destinationFilesystem,
        string $destinationPath,
        array $config = []
    ): void {
        $visibility = $visibility ?? $sourceFilesystem->visibility($sourcePath);
        $stream = $sourceFilesystem->readStream($sourcePath);
        $destinationFilesystem->writeStream(
            $destinationPath,
            $stream,
            !empty($config)
                ? array_merge($config, compact('visibility'))
                : compact('visibility')
        );
    }

    /**
     * Move file in one filesystem
     *
     * @param FilesystemOperator $sourceFilesystem
     * @param string $sourcePath
     * @param string $destinationPath
     * @param array $config
     *
     * @throws FilesystemException
     */
    protected function moveInTheSameFilesystem(
        FilesystemOperator $sourceFilesystem,
        string $sourcePath,
        string $destinationPath,
        array $config = []
    ): void {
        if ($sourcePath === $destinationPath && empty($config)) {
            return;
        }

        $sourceFilesystem->move($sourcePath, $destinationPath, $config);
    }

    /**
     * Move file across different filesystems
     *
     * @param string $sourceUri
     * @param string $destinationFileSystem
     * @param string|null $targetFilePath
     * @param array $config
     *
     * @throws StorageException
     */
    protected function moveAcrossFilesystems(
        string $sourceUri,
        string $destinationFileSystem,
        ?string $targetFilePath = null,
        array $config = []
    ): void {
        $this->copy($sourceUri, $destinationFileSystem, $targetFilePath, $config);
        $this->delete($sourceUri);
    }
}
