<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @param DateIntervalFormat $duration
     */
    public function withExpirationDate($duration): self;
}
