<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 *
 * @psalm-import-type IdType from StorageInterface
 * @see StorageInterface
 */
interface ReadableInterface
{
    /**
     * {@see BucketInterface::getContents()}
     *
     * @param IdType $id
     * @return string
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getContents($id): string;

    /**
     * {@see BucketInterface::getStream()}
     *
     * @param IdType $id
     * @return resource
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getStream($id);

    /**
     * {@see BucketInterface::exists()}
     *
     * @param IdType $id
     * @return bool
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function exists($id): bool;

    /**
     * {@see BucketInterface::getLastModified()}
     *
     * @param IdType $id
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getLastModified($id): int;

    /**
     * {@see BucketInterface::getSize()}
     *
     * @param IdType $id
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getSize($id): int;

    /**
     *{@see BucketInterface::getMimeType()}
     *
     * @param IdType $id
     * @return string
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getMimeType($id): string;

    /**
     * {@see BucketInterface::getVisibility()}
     *
     * @param IdType $id
     * @return VisibilityType
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility($id): string;
}
