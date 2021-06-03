<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\Attributes\Internal\CachedReader;
use Spiral\Attributes\Internal\Key\KeyGeneratorInterface;

final class Psr16CachedReader extends CachedReader
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param KeyGeneratorInterface|null $generator
     */
    public function __construct(ReaderInterface $reader, CacheInterface $cache, KeyGeneratorInterface $generator = null)
    {
        $this->cache = $cache;

        parent::__construct($reader, $generator);
    }

    /**
     * {@inheritDoc}
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
