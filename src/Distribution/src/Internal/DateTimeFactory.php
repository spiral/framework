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
 * @internal DateTimeFactory is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 */
final class DateTimeFactory implements DateTimeFactoryInterface
{
    /**
     * @var string
     */
    private const DEFAULT_TIMEZONE = 'UTC';

    /**
     * @var string
     */
    private const DATE_NOW = 'now';

    /**
     * @var string
     */
    private $timezone;

    /**
     * @param string $timezone
     */
    public function __construct(string $timezone = self::DEFAULT_TIMEZONE)
    {
        $this->timezone = new \DateTimeZone($timezone);
    }

    /**
     * {@inheritDoc}
     */
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::DATE_NOW, $this->timezone);
    }
}
