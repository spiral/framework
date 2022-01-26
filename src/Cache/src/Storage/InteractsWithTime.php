<?php

declare(strict_types=1);

namespace Spiral\Cache\Storage;

use Spiral\Cache\Exception\InvalidArgumentException;

trait InteractsWithTime
{
    /**
     * @param null|int|\DateInterval|\DateTimeInterface $ttl
     * @return int
     * @throws InvalidArgumentException
     */
    private function ttlToTimestamp($ttl): int
    {
        if ($ttl === null) {
            return $this->ttl + time();
        }

        if ($ttl instanceof \DateInterval) {
            return $this->now()
                ->add($ttl)
                ->getTimestamp();
        }

        if ($ttl instanceof \DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        if (\is_int($ttl)) {
            $now = $this->now();

            return $now->getTimestamp() + $ttl;
        }

        throw new InvalidArgumentException(
            \sprintf(
                'Cache item ttl (expiration) must be of type int or \DateInterval, but %s passed',
                \get_debug_type($ttl)
            )
        );
    }

    /**
     * Please note that this interface currently emulates the behavior of the
     * PSR-20 implementation and may be replaced by the `psr/clock`
     * implementation in future versions.
     *
     * Returns the current time as a DateTimeImmutable instance.
     *
     * @codeCoverageIgnore Ignore time-aware-mutable value.
     *                     Must be covered with a stub.
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    protected function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('NOW');
    }
}
