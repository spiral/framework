<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\AbstractChecker;
use Spiral\Validation\Checker\DatetimeChecker\ThresholdChecker;

/**
 * @inherit-messages
 */
final class DatetimeChecker extends AbstractChecker implements SingletonInterface
{
    public const MESSAGES = [
        'future'   => '[[Should be a date in the future.]]',
        'past'     => '[[Should be a date in the past.]]',
        'valid'    => '[[Not a valid date.]]',
        'format'   => '[[Value should match the specified date format {1}.]]',
        'timezone' => '[[Not a valid timezone.]]',
        'before'   => '[[Value {1} should come before value {2}.]]',
        'after'    => '[[Value {1} should come after value {2}.]]',
    ];
    //Possible format mapping
    private const MAP_FORMAT = [
        'c' => 'Y-m-d\TH:i:sT',
    ];

    private readonly ThresholdChecker $threshold;

    public function __construct(
        private \Closure|\DateTimeInterface|string|int|null $now = null
    ) {
        $this->threshold = new DatetimeChecker\ThresholdChecker();
    }

    /**
     * Check if date is in the future. Do not compare if the current date is invalid.
     */
    public function future(mixed $value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->after($this->date($value), $this->now(), $orNow, $useMicroSeconds);
    }

    /**
     * Check if date is in the past. Do not compare if the current date is invalid.
     */
    public function past(mixed $value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->before($this->date($value), $this->now(), $orNow, $useMicroSeconds);
    }

    /**
     * Check if date format matches the provided one.
     */
    public function format(mixed $value, string $format): bool
    {
        if (!$this->isApplicableValue($value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat(self::MAP_FORMAT[$format] ?? $format, (string)$value);

        return $date !== false;
    }

    /**
     * Check if date is valid. Empty values are acceptable.
     */
    public function valid(mixed $value): bool
    {
        return $this->date($value) !== null;
    }

    /**
     * Value has to be a valid timezone.
     */
    public function timezone(mixed $value): bool
    {
        if (!\is_scalar($value)) {
            return false;
        }

        return \in_array((string)$value, \DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Check if date comes before the given one. Do not compare if the given date is missing or invalid.
     */
    public function before(mixed $value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->before($this->date($value), $this->fromField($field), $orEquals, $useMicroSeconds);
    }

    /**
     * Check if date comes after the given one. Do not compare if the given date is missing or invalid.
     */
    public function after(mixed $value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->after($this->date($value), $this->fromField($field), $orEquals, $useMicroSeconds);
    }

    /**
     * @return \DateTimeInterface
     */
    private function now(): ?\DateTimeInterface
    {
        try {
            return $this->date($this->now ?: 'now');
        } catch (\Throwable) {
            //here's the fail;
        }

        return null;
    }

    private function date(mixed $value): ?\DateTimeInterface
    {
        if (\is_callable($value)) {
            $value = $value();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if (!$this->isApplicableValue($value)) {
            return null;
        }

        try {
            if (!$value) {
                $value = '0';
            }

            return new \DateTimeImmutable(\is_numeric($value) ? \sprintf('@%d', $value) : \trim($value));
        } catch (\Throwable) {
            //here's the fail;
        }

        return null;
    }

    private function isApplicableValue(mixed $value): bool
    {
        return \is_string($value) || \is_numeric($value);
    }

    private function fromField(string $field): ?\DateTimeInterface
    {
        $before = $this->getValidator()->getValue($field);
        if ($before !== null) {
            return $this->date($before);
        }

        return null;
    }
}
