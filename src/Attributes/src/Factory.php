<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\Reader as DoctrineReaderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Composite\SelectiveReader;

class Factory implements FactoryInterface
{
    /**
     * @var CacheInterface|CacheItemPoolInterface|null
     */
    private $cache;

    /**
     * @param CacheInterface|CacheItemPoolInterface|null $cache
     * @return $this
     */
    public function withCache($cache): self
    {
        assert($cache instanceof CacheItemPoolInterface || $cache instanceof CacheInterface || $cache === null);

        $self = clone $this;
        $self->cache = $cache;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): ReaderInterface
    {
        return $this->decorateByCache(
            $this->decorateByAnnotations(
                new AttributeReader()
            )
        );
    }

    /**
     * @param ReaderInterface $reader
     * @return ReaderInterface
     */
    private function decorateByAnnotations(ReaderInterface $reader): ReaderInterface
    {
        if (\interface_exists(DoctrineReaderInterface::class)) {
            $doctrine = new AnnotationReader(new DoctrineAnnotationReader());

            $reader = new SelectiveReader([$reader, $doctrine]);
        }

        return $reader;
    }

    /**
     * @param ReaderInterface $reader
     * @return ReaderInterface
     */
    private function decorateByCache(ReaderInterface $reader): ReaderInterface
    {
        switch (true) {
            case $this->cache instanceof CacheInterface:
                return new Psr16CachedReader($reader, $this->cache);

            case $this->cache instanceof CacheItemPoolInterface:
                return new Psr6CachedReader($reader, $this->cache);

            default:
                return $reader;
        }
    }
}
