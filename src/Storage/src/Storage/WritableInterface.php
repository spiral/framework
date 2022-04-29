<?php

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
 * @see StorageInterface
 */
interface WritableInterface
{
    /**
     * {@see BucketInterface::create()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function create(string|\Stringable $id, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::write()}
     *
     * @param string|\Stringable|resource $content
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function write(string|\Stringable $id, mixed $content, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::setVisibility()}
     *
     * @param VisibilityType $visibility
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function setVisibility(
        string|\Stringable $id,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface;

    /**
     * {@see BucketInterface::copy()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function copy(
        string|\Stringable $source,
        string|\Stringable $destination,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::move()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function move(
        string|\Stringable $source,
        string|\Stringable $destination,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::delete()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function delete(string|\Stringable $id, bool $clean = false): void;
}
