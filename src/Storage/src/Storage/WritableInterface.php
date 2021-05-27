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
use Spiral\Storage\FileInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 *
 * @psalm-import-type IdType from StorageInterface
 * @see StorageInterface
 */
interface WritableInterface
{
    /**
     * {@see BucketInterface::create()}
     *
     * @param IdType $id
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function create($id, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::write()}
     *
     * @param IdType $id
     * @param string|\Stringable|resource $content
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function write($id, $content, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::setVisibility()}
     *
     * @param IdType $id
     * @param VisibilityType $visibility
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function setVisibility(
        $id,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface;

    /**
     * {@see BucketInterface::copy()}
     *
     * @param IdType $source
     * @param IdType $destination
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function copy($source, $destination, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::move()}
     *
     * @param IdType $source
     * @param IdType $destination
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function move($source, $destination, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::delete()}
     *
     * @param IdType $id
     * @param bool $clean
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function delete($id, bool $clean = false): void;
}
