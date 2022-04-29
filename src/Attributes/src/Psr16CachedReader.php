<?php

declare(strict_types=1);

namespace Spiral\Attributes;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\Attributes\Internal\CachedReader;
use Spiral\Attributes\Internal\Key\KeyGeneratorInterface;

final class Psr16CachedReader extends CachedReader
{
    public function __construct(
        ReaderInterface $reader,
        private readonly CacheInterface $cache,
        KeyGeneratorInterface $generator = null
    ) {
        parent::__construct($reader, $generator);
    }

    /**
     * @psalm-suppress InvalidThrow
     * @throws InvalidArgumentException
     */
    protected function cached(string $key, callable $then): iterable
    {
        if (!$this->cache->has($key)) {
            $this->cache->set($key, $then());
        }

        return $this->cache->get($key);
    }
}
