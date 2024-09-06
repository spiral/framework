<?php

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

/**
 * @internal DateTimeFactory is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 */
final class DateTimeFactory implements DateTimeFactoryInterface
{
    private const DEFAULT_TIMEZONE = 'UTC';
    private const DATE_NOW = 'now';

    private readonly \DateTimeZone $timezone;

    public function __construct(string $timezone = self::DEFAULT_TIMEZONE)
    {
        $this->timezone = new \DateTimeZone($timezone);
    }

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::DATE_NOW, $this->timezone);
    }
}
