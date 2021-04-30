<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Parser;

use Spiral\Storage\Exception\UriException;

final class Uri implements UriInterface
{
    /**
     * @var string
     */
    private const SCHEME_PATH_DELIMITER = '://';

    /**
     * @var string
     */
    private const PATH_DELIMITER = '/';

    /**
     * @var string
     */
    private $fs;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $fs
     * @param string $path
     * @throws UriException
     */
    public function __construct(string $fs, string $path)
    {
        $this->setFileSystem($fs);
        $this->setPath($path);
    }

    /**
     * @param string $fs
     * @param string $path
     * @return static
     * @throws UriException
     */
    public static function create(string $fs, string $path): self
    {
        return new self($fs, $path);
    }

    /**
     * @param string $fs
     * @throws UriException
     */
    private function setFileSystem(string $fs): void
    {
        if ($fs === '') {
            throw new UriException('Filesystem name can not be empty');
        }

        $this->fs = $fs;
    }

    /**
     * @param string $path
     * @throws UriException
     */
    private function setPath(string $path): void
    {
        $path = \str_replace(['\\', '/'], self::PATH_DELIMITER, $path);
        $path = \trim(\trim($path), self::PATH_DELIMITER);

        if ($path === '') {
            throw new UriException('Filesystem pathname can not be empty');
        }

        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function withFileSystem(string $fs): UriInterface
    {
        $self = clone $this;
        $self->setFileSystem($fs);

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(string $path): UriInterface
    {
        $self = clone $this;
        $self->setPath($path);

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function getFileSystem(): string
    {
        return $this->fs;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->getFileSystem() . self::SCHEME_PATH_DELIMITER . $this->getPath();
    }
}
