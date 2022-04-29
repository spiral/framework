<?php

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

/**
 * @internal DateTimeIntervalFactoryInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 *
 * @psalm-type DateIntervalFormat = string|int|\DateInterval|\DateTimeInterface
 */
interface DateTimeIntervalFactoryInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function create(mixed $duration): \DateInterval;

    public function toDateTime(\DateInterval $interval): \DateTimeImmutable;
}
