<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\File;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface WritableInterface extends EntryInterface
{
    /**
     * {@see StorageInterface::create()}
     *
     * @param array $config
     * @return $this
     * @throws FileOperationException
     */
    public function create(array $config = []): self;

    /**
     * {@see StorageInterface::write()}
     *
     * @param resource|string|\Stringable $content
     * @param array $config
     * @return $this
     * @throws FileOperationException
     */
    public function write($content, array $config = []): self;

    /**
     * {@see StorageInterface::setVisibility()}
     *
     * @param VisibilityType $visibility
     * @return $this
     * @throws FileOperationException
     */
    public function setVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): self;

    /**
     * {@see StorageInterface::copy()}
     *
     * @param string $pathname
     * @param StorageInterface|null $storage
     * @param array $config
     * @return EntryInterface
     * @throws FileOperationException
     */
    public function copy(
        string $pathname,
        StorageInterface $storage = null,
        array $config = []
    ): EntryInterface;

    /**
     * {@see StorageInterface::move()}
     *
     * @param string $pathname
     * @param StorageInterface|null $storage
     * @param array $config
     * @return EntryInterface
     * @throws FileOperationException
     */
    public function move(
        string $pathname,
        StorageInterface $storage = null,
        array $config = []
    ): EntryInterface;

    /**
     * {@see StorageInterface::delete()}
     *
     * @param bool $clean
     * @throws FileOperationException
     */
    public function delete(bool $clean = false): void;
}
