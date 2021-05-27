<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage\ReadableTrait;
use Spiral\Storage\Storage\WritableTrait;

/**
 * @psalm-import-type IdType from StorageInterface
 * @see StorageInterface
 */
final class Storage implements MutableStorageInterface
{
    use ReadableTrait;
    use WritableTrait;

    /**
     * @var string
     */
    public const DEFAULT_STORAGE = 'default';

    /**
     * @var string
     */
    private const ERROR_REDEFINITION = 'Can not redefine already defined bucket `%s`';

    /**
     * @var string
     */
    private const ERROR_NOT_FOUND = 'Bucket `%s` has not been defined';

    /**
     * @var array<string, BucketInterface>
     */
    private $buckets = [];

    /**
     * @var string
     */
    private $default;

    /**
     * @param string $name
     */
    public function __construct(string $name = self::DEFAULT_STORAGE)
    {
        $this->default = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function withDefault(string $name): StorageInterface
    {
        $self = clone $this;
        $self->default = $name;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function bucket(string $name = null): BucketInterface
    {
        $name = $name ?? $this->default;

        if (!isset($this->buckets[$name])) {
            throw new InvalidArgumentException(\sprintf(self::ERROR_NOT_FOUND, $name));
        }

        return $this->buckets[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function file($id): FileInterface
    {
        [$bucket, $file] = $this->parseUri($id);

        return $this->bucket($bucket)->file($file);
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $name, BucketInterface $storage, bool $overwrite = false): void
    {
        if ($overwrite === false && isset($this->buckets[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_REDEFINITION, $name));
        }

        $this->buckets[$name] = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->buckets);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->buckets);
    }

    /**
     * @param IdType $uri
     * @param bool $withScheme
     * @return array{0: string|null, 1: string}
     * @throws InvalidArgumentException
     */
    protected function parseUri($uri, bool $withScheme = true): array
    {
        $uri = $this->uriToString($uri);
        $result = \parse_url($uri);

        if ($result === false) {
            $message = 'URI argument must be a valid URI in "[STORAGE]://[PATH_TO_FILE]" format, but `%s` given';
            throw new InvalidArgumentException(\sprintf($message, $uri));
        }

        if (!isset($result['scheme'])) {
            $result['scheme'] = $withScheme ? $this->default : null;
        }

        if (!isset($result['host'])) {
            $result['host'] = '';
        }

        return [
            $result['scheme'] ?? null,
            $result['host'] . \rtrim($result['path'] ?? '', '/'),
        ];
    }

    /**
     * @param IdType $uri
     * @return string
     * @throws InvalidArgumentException
     */
    private function uriToString($uri): string
    {
        switch (true) {
            case $uri instanceof UriInterface:
            case $uri instanceof \Stringable:
            case \is_object($uri) && \method_exists($uri, '__toString'):
                return (string)$uri;

            case \is_string($uri):
                return $uri;

            default:
                $message = 'File URI must be a string or instance of Stringable interface, but %s given';
                throw new InvalidArgumentException(\sprintf($message, \get_debug_type($uri)));
        }
    }
}
