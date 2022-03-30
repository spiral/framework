<?php

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 *
 * @see StorageInterface
 */
interface ReadableInterface
{
    /**
     * {@see BucketInterface::getContents()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getContents(string|UriInterface|\Stringable $id): string;

    /**
     * {@see BucketInterface::getStream()}
     *
     * @return resource
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getStream(string|UriInterface|\Stringable $id);

    /**
     * {@see BucketInterface::exists()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function exists(string|UriInterface|\Stringable $id): bool;

    /**
     * {@see BucketInterface::getLastModified()}
     *
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getLastModified(string|UriInterface|\Stringable $id): int;

    /**
     * {@see BucketInterface::getSize()}
     *
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getSize(string|UriInterface|\Stringable $id): int;

    /**
     *{@see BucketInterface::getMimeType()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getMimeType(string|UriInterface|\Stringable $id): string;

    /**
     * {@see BucketInterface::getVisibility()}
     *
     * @return VisibilityType
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string|UriInterface|\Stringable $id): string;
}
