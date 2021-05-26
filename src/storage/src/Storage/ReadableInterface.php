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
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface ReadableInterface
{
    /**
     * Reads file by relative pathname and return its content as string.
     *
     * @param string $pathname
     * @return string
     * @throws FileOperationException
     */
    public function getContents(string $pathname): string;

    /**
     * Reads file by relative pathname and return its content as resource stream.
     *
     * @param string $pathname
     * @return resource
     * @throws FileOperationException
     */
    public function getStream(string $pathname);

    /**
     * Checks file for existing.
     *
     * @param string $pathname
     * @return bool
     * @throws FileOperationException
     */
    public function exists(string $pathname): bool;

    /**
     * Returns the timestamp of last file modification.
     *
     * @param string $pathname
     * @return positive-int|0
     * @throws FileOperationException
     */
    public function getLastModified(string $pathname): int;

    /**
     * Returns the file size in bytes.
     *
     * @param string $pathname
     * @return positive-int|0
     * @throws FileOperationException
     */
    public function getSize(string $pathname): int;

    /**
     * Returns the file mime type.
     *
     * @param string $pathname
     * @return string
     * @throws FileOperationException
     */
    public function getMimeType(string $pathname): string;

    /**
     * Returns the file visibility.
     *
     * @param string $pathname
     * @return VisibilityType
     * @throws FileOperationException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string $pathname): string;
}
