<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Spiral\Attributes\Internal\CachedReader;
use Spiral\Attributes\Internal\Key\KeyGeneratorInterface;

final class Psr6CachedReader extends CachedReader
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param ReaderInterface $reader
     * @param CacheItemPoolInterface $cache
     * @param KeyGeneratorInterface|null $generator
     */
    public function __construct(
        ReaderInterface $reader,
        CacheItemPoolInterface $cache,
        KeyGeneratorInterface $generator = null
    ) {
        $this->cache = $cache;

        parent::__construct($reader, $generator);
    }

    /**
     * {@inheritDoc}
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
