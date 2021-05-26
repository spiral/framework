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
use Spiral\Storage\Manager\ReadableTrait;
use Spiral\Storage\Manager\WritableTrait;

/**
 * @psalm-type UriType = string | UriInterface | \Stringable
 * @see UriInterface
 */
final class Manager implements MutableManagerInterface
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
    private const ERROR_REDEFINITION = 'Can not redefine already defined storage `%s`';

    /**
     * @var string
     */
    private const ERROR_NOT_FOUND = 'Storage `%s` has not been defined';

    /**
     * @var array<string, StorageInterface>
     */
    private $storages = [];

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
    public function withDefault(string $name): ManagerInterface
    {
        $self = clone $this;
        $self->default = $name;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function storage(string $name = null): StorageInterface
    {
        $name = $name ?? $this->default;

        if (!isset($this->storages[$name])) {
            throw new InvalidArgumentException(\sprintf(self::ERROR_NOT_FOUND, $name));
        }

        return $this->storages[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $name, StorageInterface $storage, bool $overwrite = false): void
    {
        if ($overwrite === false && isset($this->storages[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_REDEFINITION, $name));
        }

        $this->storages[$name] = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->storages);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->storages);
    }

    /**
     * @param UriType $uri
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
     * @param UriType $uri
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
                $message = 'URI must be a string or instance of Stringable interface, but %s given';
                throw new InvalidArgumentException(\sprintf($message, \get_debug_type($uri)));
        }
    }
}
