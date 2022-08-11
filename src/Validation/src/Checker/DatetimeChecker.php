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
        'c' => 'Y-m-d\TH:i:sT',
    ];

    /** @var callable|\DateTimeInterface|string|numeric|null */
    private $now;
    /** @var DatetimeChecker\ThresholdChecker */
    private $threshold;

    public function __construct($now = null)
    {
        $this->now = $now;
        $this->threshold = new DatetimeChecker\ThresholdChecker();
    }

    /**
     * Check if date is in the future. Do not compare if the current date is invalid.
     *
     * @param mixed $value
     */
    public function future($value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->after($this->date($value), $this->now(), $orNow, $useMicroSeconds);
    }

    /**
     * Check if date is in the past. Do not compare if the current date is invalid.
     *
     * @param mixed $value
     */
    public function past($value, bool $orNow = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->before($this->date($value), $this->now(), $orNow, $useMicroSeconds);
    }

    /**
     * Check if date format matches the provided one.
     *
     * @param mixed  $value
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
     */
    public function valid($value): bool
    {
        return $this->date($value) !== null;
    }

    /**
     * Value has to be a valid timezone.
     *
     * @param mixed $value
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
     */
    public function before($value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
    {
        return $this->threshold->before($this->date($value), $this->fromField($field), $orEquals, $useMicroSeconds);
    }

    /**
     * Check if date comes after the given one. Do not compare if the given date is missing or invalid.
     *
     * @param mixed  $value
     */
    public function after($value, string $field, bool $orEquals = false, bool $useMicroSeconds = false): bool
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
        } catch (\Throwable $e) {
            //here's the fail;
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function date($value): ?\DateTimeInterface
    {
        if (is_callable($value)) {
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

            return new \DateTimeImmutable(is_numeric($value) ? sprintf('@%d', $value) : trim($value));
        } catch (\Throwable $e) {
            //here's the fail;
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function isApplicableValue($value): bool
    {
        return is_string($value) || is_numeric($value);
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
