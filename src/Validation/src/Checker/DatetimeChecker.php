<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\AbstractChecker;

/**
 * @inherit-messages
 */
final class DatetimeChecker extends AbstractChecker implements SingletonInterface
{

    /**
     * {@inheritdoc}
     */
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
        'c' => 'Y-m-d\TH:i:sT'
    ];

    /**
     * Check if date is in the future. Do not compare if the current date is invalid.
     *
     * @param mixed $value
     * @param bool  $orNow
     * @param bool  $useMicroSeconds
     * @return bool
     */
    public function future($value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        $compare = $this->compare($this->date($value), $this->now(), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orNow ? $compare >= 0 : $compare > 0;
    }

    /**
     * Check if date is in the past. Do not compare if the current date is invalid.
     *
     * @param mixed $value
     * @param bool  $orNow
     * @param bool  $useMicroSeconds
     * @return bool
     */
    public function past($value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        $compare = $this->compare($this->date($value), $this->now(), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orNow ? $compare <= 0 : $compare < 0;
    }

    /**
     * Check if date format matches the provided one.
     *
     * @param mixed  $value
     * @param string $format
     * @return bool
     */
    public function format($value, string $format): bool
    {
        if (!$this->isApplicableValue($value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat(self::MAP_FORMAT[$format] ?? $format, (string)$value);

        return $date !== false;
    }

    /**
     * Check if date is valid. Empty values are acceptable.
     *
     * @param mixed $value
     * @return bool
     */
    public function valid($value): bool
    {
        return $this->date($value) !== null;
    }

    /**
     * Value has to be a valid timezone.
     *
     * @param mixed $value
     * @return bool
     */
    public function timezone($value): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        return in_array((string)$value, \DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Check if date comes before the given one. Do not compare if the given date is missing or invalid.
     *
     * @param mixed  $value
     * @param string $field
     * @param bool   $orEquals
     * @param bool   $useMicroSeconds
     * @return bool
     */
    public function before($value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
    {
        $compare = $this->compare($this->date($value), $this->thresholdFromField($field), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orEquals ? $compare <= 0 : $compare < 0;
    }

    /**
     * Check if date comes after the given one. Do not compare if the given date is missing or invalid.
     *
     * @param mixed  $value
     * @param string $field
     * @param bool   $orEquals
     * @param bool   $useMicroSeconds
     * @return bool
     */
    public function after($value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
    {
        $compare = $this->compare($this->date($value), $this->thresholdFromField($field), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orEquals ? $compare >= 0 : $compare > 0;
    }

    /**
     * @param mixed $value
     * @return \DateTimeImmutable|null
     */
    private function date($value): ?\DateTimeImmutable
    {
        if (!$this->isApplicableValue($value)) {
            return null;
        }

        try {
            if (empty($value)) {
                $value = '0';
            }

            return new \DateTimeImmutable(is_numeric($value) ? ('@' . (int)$value) : (string)$value);
        } catch (\Throwable $e) {
            //here's the fail;
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isApplicableValue($value): bool
    {
        return is_string($value) || is_numeric($value);
    }

    /**
     * @return \DateTimeImmutable
     */
    private function now(): ?\DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable('now');
        } catch (\Throwable $e) {
            //here's the fail;
        }

        return null;
    }

    /**
     * @param string $field
     * @return \DateTimeImmutable|null
     */
    private function thresholdFromField(string $field): ?\DateTimeImmutable
    {
        $before = $this->getValidator()->getValue($field);
        if ($before !== null) {
            return $this->date($before);
        }

        return null;
    }

    /**
     * @param \DateTimeImmutable|null $date
     * @param \DateTimeImmutable|null $threshold
     * @param bool                    $useMicroseconds
     * @return bool|int
     */
    private function compare(?\DateTimeImmutable $date, ?\DateTimeImmutable $threshold, bool $useMicroseconds)
    {
        if ($date === null) {
            return false;
        }

        if ($threshold === null) {
            return true;
        }

        if (!$useMicroseconds) {
            $date = $this->dropMicroSeconds($date);
            $threshold = $this->dropMicroSeconds($threshold);
        }

        return $date <=> $threshold;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return \DateTimeImmutable
     */
    private function dropMicroSeconds(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime(
            (int)$date->format('H'),
            (int)$date->format('i'),
            (int)$date->format('s')
        );
    }
}
