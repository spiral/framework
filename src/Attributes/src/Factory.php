<?php

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\Reader as DoctrineReaderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Attributes\Composite\SelectiveReader;

class Factory implements FactoryInterface
{
    private CacheInterface|CacheItemPoolInterface|null $cache = null;

    public function withCache(CacheInterface|CacheItemPoolInterface|null $cache): self
    {
        \assert($cache instanceof CacheItemPoolInterface || $cache instanceof CacheInterface || $cache === null);

        $self = clone $this;
        $self->cache = $cache;

        return $self;
    }

    public function create(): ReaderInterface
    {
        return $this->decorateByCache(
            $this->decorateByAnnotations(
                new AttributeReader()
            )
        );
    }

    private function decorateByAnnotations(ReaderInterface $reader): ReaderInterface
    {
        if (\interface_exists(DoctrineReaderInterface::class)) {
            $doctrine = new AnnotationReader(new DoctrineAnnotationReader());

            $reader = new SelectiveReader([$reader, $doctrine]);
        }

        return $reader;
    }

    private function decorateByCache(ReaderInterface $reader): ReaderInterface
    {
        return match (true) {
            $this->cache instanceof CacheInterface => new Psr16CachedReader($reader, $this->cache),
            $this->cache instanceof CacheItemPoolInterface => new Psr6CachedReader($reader, $this->cache),
            default => $reader,
        };
    }
}
