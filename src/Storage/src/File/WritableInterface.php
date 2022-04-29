<?php

declare(strict_types=1);

namespace Spiral\Storage\File;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\FileInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface WritableInterface extends EntryInterface
{
    /**
     * {@see BucketInterface::create()}
     *
     * @throws FileOperationException
     */
    public function create(array $config = []): FileInterface;

    /**
     * {@see BucketInterface::write()}
     *
     * @param resource|string|\Stringable $content
     * @throws FileOperationException
     */
    public function write(mixed $content, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::setVisibility()}
     *
     * @param VisibilityType $visibility
     * @throws FileOperationException
     */
    public function setVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface;

    /**
     * {@see BucketInterface::copy()}
     *
     * @param BucketInterface|null $storage
     * @throws FileOperationException
     */
    public function copy(
        string $pathname,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::move()}
     *
     * @param BucketInterface|null $storage
     * @throws FileOperationException
     */
    public function move(
        string $pathname,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::delete()}
     *
     * @throws FileOperationException
     */
    public function delete(bool $clean = false): void;
}
