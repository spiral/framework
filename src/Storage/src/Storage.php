<?php

declare(strict_types=1);

namespace Spiral\Storage;

use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage\ReadableTrait;
use Spiral\Storage\Storage\WritableTrait;

/**
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
    private array $buckets = [];
    private string $default;

    public function __construct(string $name = self::DEFAULT_STORAGE)
    {
        $this->default = $name;
    }

    public function withDefault(string $name): StorageInterface
    {
        $self = clone $this;
        $self->default = $name;

        return $self;
    }

    public function bucket(string $name = null): BucketInterface
    {
        $name ??= $this->default;

        if (!isset($this->buckets[$name])) {
            throw new InvalidArgumentException(\sprintf(self::ERROR_NOT_FOUND, $name));
        }

        return $this->buckets[$name];
    }

    public function file(string|\Stringable $id): FileInterface
    {
        [$bucket, $file] = $this->parseUri($id);

        return $this->bucket($bucket)->file($file);
    }

    public function add(string $name, BucketInterface $storage, bool $overwrite = false): void
    {
        if (!$overwrite && isset($this->buckets[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_REDEFINITION, $name));
        }

        $this->buckets[$name] = $storage;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->buckets);
    }

    public function count(): int
    {
        return \count($this->buckets);
    }

    /**
     * @return array{0: string|null, 1: string}
     * @throws InvalidArgumentException
     */
    protected function parseUri(string|\Stringable $uri, bool $withScheme = true): array
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

    private function uriToString(string|\Stringable $uri): string
    {
        return match (true) {
            \is_string($uri) => $uri,
            default => (string) $uri
        };
    }
}
