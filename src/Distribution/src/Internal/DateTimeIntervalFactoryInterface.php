<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param DateIntervalFormat|null $duration
     * @return \DateInterval
     * @throws \InvalidArgumentException
     */
    public function create($duration): \DateInterval;

    /**
     * @param \DateInterval $interval
     * @return \DateTimeImmutable
     */
    public function toDateTime(\DateInterval $interval): \DateTimeImmutable;
}
