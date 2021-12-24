<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use JetBrains\PhpStorm\ExpectedValues;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Visibility;

/**
 * @mixin ReadableInterface
 */
trait ReadableTrait
{
    /**
     * {@inheritDoc}
     */
    public function exists(string $pathname): bool
    {
        $fs = $this->getOperator();

        try {
            return $fs->fileExists($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $pathname): string
    {
        $fs = $this->getOperator();

        try {
            return $fs->read($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(string $pathname)
    {
        $fs = $this->getOperator();

        try {
            return $fs->readStream($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLastModified(string $pathname): int
    {
        $fs = $this->getOperator();

        try {
            return $fs->lastModified($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(string $pathname): int
    {
        $fs = $this->getOperator();

        try {
            return $fs->fileSize($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType(string $pathname): string
    {
        $fs = $this->getOperator();

        try {
            return $fs->mimeType($pathname);
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string $pathname): string
    {
        $fs = $this->getOperator();

        try {
            return $this->fromFlysystemVisibility(
                $fs->visibility($pathname)
            );
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    abstract protected function getOperator(): FilesystemOperator;

    #[ExpectedValues(valuesFromClass: Visibility::class)]
    private function fromFlysystemVisibility(
        #[ExpectedValues(valuesFromClass: \League\Flysystem\Visibility::class)]
        string $visibility
    ): string {
        return $visibility === \League\Flysystem\Visibility::PUBLIC
            ? Visibility::VISIBILITY_PUBLIC
            : Visibility::VISIBILITY_PRIVATE;
    }
}
