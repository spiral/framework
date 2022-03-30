<?php

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Message\UriInterface;
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
    public function create(string|UriInterface|\Stringable $id, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::write()}
     *
     * @param string|\Stringable|resource $content
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function write(string|UriInterface|\Stringable $id, mixed $content, array $config = []): FileInterface;

    /**
     * {@see BucketInterface::setVisibility()}
     *
     * @param VisibilityType $visibility
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function setVisibility(
        string|UriInterface|\Stringable $id,
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
        string|UriInterface|\Stringable $source,
        string|UriInterface|\Stringable $destination,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::move()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function move(
        string|UriInterface|\Stringable $source,
        string|UriInterface|\Stringable $destination,
        array $config = []
    ): FileInterface;

    /**
     * {@see BucketInterface::delete()}
     *
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function delete(string|UriInterface|\Stringable $id, bool $clean = false): void;
}
