<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker\DatetimeChecker;

class ThresholdChecker
{
    /**
     * Check if date comes before the given one. Do not compare if the given date is missing or invalid.
     */
    public function before(
        ?\DateTimeInterface $value,
        ?\DateTimeInterface $threshold,
        bool $orEquals = false,
        bool $useMicroSeconds = false
    ): bool {
        $compare = $this->compare($this->date($value), $this->date($threshold), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orEquals ? $compare <= 0 : $compare < 0;
    }

    /**
     * Check if date comes after the given one. Do not compare if the given date is missing or invalid.
     */
    public function after(
        ?\DateTimeInterface $value,
        ?\DateTimeInterface $threshold,
        bool $orEquals = false,
        bool $useMicroSeconds = false
    ): bool {
        $compare = $this->compare($this->date($value), $this->date($threshold), $useMicroSeconds);
        if (is_bool($compare)) {
            return $compare;
        }

        return $orEquals ? $compare >= 0 : $compare > 0;
    }

    /**
     * @param mixed $value
     */
    private function date($value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($value);
        }

        return null;
    }

    /**
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

    private function dropMicroSeconds(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime(
            (int)$date->format('H'),
            (int)$date->format('i'),
            (int)$date->format('s')
        );
    }
}
