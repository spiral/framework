<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Manager;

use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 *
 * @psalm-type UriType = string | UriInterface | \Stringable
 * @see UriInterface
 */
interface ReadableInterface
{
    /**
     * {@see StorageInterface::getContents()}
     *
     * @param UriType $uri
     * @return string
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getContents($uri): string;

    /**
     * {@see StorageInterface::getStream()}
     *
     * @param UriType $uri
     * @return resource
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getStream($uri);

    /**
     * {@see StorageInterface::exists()}
     *
     * @param UriType $uri
     * @return bool
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function exists($uri): bool;

    /**
     * {@see StorageInterface::getLastModified()}
     *
     * @param UriType $uri
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getLastModified($uri): int;

    /**
     * {@see StorageInterface::getSize()}
     *
     * @param UriType $uri
     * @return positive-int|0
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getSize($uri): int;

    /**
     *{@see StorageInterface::getMimeType()}
     *
     * @param UriType $uri
     * @return string
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function getMimeType($uri): string;

    /**
     * {@see StorageInterface::getVisibility()}
     *
     * @param UriType $uri
     * @return VisibilityType
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility($uri): string;
}
