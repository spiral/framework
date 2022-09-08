<?php

declare(strict_types=1);

namespace Spiral\Distribution;

use Spiral\Distribution\Internal\DateTimeIntervalFactoryInterface;

/**
 * @psalm-import-type DateIntervalFormat from DateTimeIntervalFactoryInterface
 * @see DateTimeIntervalFactoryInterface
 */
interface ExpirationAwareResolverInterface
{
    public function getExpirationDate(): \DateInterval;

    public function withExpirationDate(mixed $duration): self;
}
