<?php

declare(strict_types=1);

namespace Spiral\Cache\Storage;

trait InteractsWithTime
{
    /**
     * Please note that this interface currently emulates the behavior of the
     * PSR-20 implementation and may be replaced by the `psr/clock`
     * implementation in future versions.
     *
     * Returns the current time as a DateTimeImmutable instance.
     *
     * @codeCoverageIgnore Ignore time-aware-mutable value.
     *                     Must be covered with a stub.
     */
    protected function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    private function ttlToTimestamp(null|int|\DateInterval|\DateTimeInterface $ttl = null): int
    {
        return match (true) {
            $ttl instanceof \DateInterval => $this->now()->add($ttl)->getTimestamp(),
            $ttl instanceof \DateTimeInterface => $ttl->getTimestamp(),
            $ttl === null => $this->ttl + \time(),
            default => $this->now()->getTimestamp() + $ttl
        };
    }
}
