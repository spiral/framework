<?php

declare(strict_types=1);

namespace Spiral\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Spiral\Attributes\Internal\CachedReader;
use Spiral\Attributes\Internal\Key\KeyGeneratorInterface;

final class Psr6CachedReader extends CachedReader
{
    public function __construct(
        ReaderInterface $reader,
        private readonly CacheItemPoolInterface $cache,
        KeyGeneratorInterface $generator = null
    ) {
        parent::__construct($reader, $generator);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function cached(string $key, callable $then): iterable
    {
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $this->cache->save(
                $item->set($then())
            );
        }

        return $item->get();
    }
}
