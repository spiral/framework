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
use Spiral\Storage\FileInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 *
 * @psalm-type UriType = string | UriInterface | \Stringable
 * @see UriInterface
 */
interface WritableInterface
{
    /**
     * {@see StorageInterface::create()}
     *
     * @param UriType $uri
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function create($uri, array $config = []): FileInterface;

    /**
     * {@see StorageInterface::write()}
     *
     * @param UriType $uri
     * @param string|\Stringable|resource $content
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function write($uri, $content, array $config = []): FileInterface;

    /**
     * {@see StorageInterface::setVisibility()}
     *
     * @param UriType $uri
     * @param VisibilityType $visibility
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function setVisibility(
        $uri,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface;

    /**
     * {@see StorageInterface::copy()}
     *
     * @param UriType $source
     * @param UriType $destination
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function copy($source, $destination, array $config = []): FileInterface;

    /**
     * {@see StorageInterface::move()}
     *
     * @param UriType $source
     * @param UriType $destination
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function move($source, $destination, array $config = []): FileInterface;

    /**
     * {@see StorageInterface::delete()}
     *
     * @param UriType $uri
     * @param bool $clean
     * @throws FileOperationException
     * @throws InvalidArgumentException
     */
    public function delete($uri, bool $clean = false): void;
}
