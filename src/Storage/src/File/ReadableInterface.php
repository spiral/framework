<?php

declare(strict_types=1);

namespace Spiral\Storage\File;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface ReadableInterface extends EntryInterface
{
    /**
     * {@see BucketInterface::exists()}
     *
     * @throws FileOperationException
     */
    public function exists(): bool;

    /**
     * {@see BucketInterface::getContents()}
     *
     * @throws FileOperationException
     */
    public function getContents(): string;

    /**
     * {@see BucketInterface::getStream()}
     *
     * @return resource
     * @throws FileOperationException
     */
    public function getStream();

    /**
     * {@see BucketInterface::getLastModified()}
     *
     * @return positive-int
     * @throws FileOperationException
     */
    public function getLastModified(): int;

    /**
     * {@see BucketInterface::getSize()}
     *
     * @return positive-int|0
     * @throws FileOperationException
     */
    public function getSize(): int;

    /**
     * {@see BucketInterface::getMimeType()}
     *
     * @throws FileOperationException
     */
    public function getMimeType(): string;

    /**
     * {@see BucketInterface::getVisibility()}
     *
     * @return VisibilityType
     * @throws FileOperationException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(): string;
}
