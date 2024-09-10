<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\Exception\JobException;
use Spiral\Queue\Exception\RetryableExceptionInterface;

final class RetryPolicy implements RetryPolicyInterface
{
    /**
     * @var positive-int|0
     */
    private readonly int $maxAttempts;

    /**
     * @var positive-int|0
     */
    private readonly int $delay;

    private readonly float $multiplier;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $maxAttempts, int $delay, float $multiplier = 1)
    {
        if ($maxAttempts < 0) {
            throw new InvalidArgumentException(
                \sprintf('Maximum attempts must be greater than or equal to zero: `%s` given.', $maxAttempts)
            );
        }
        $this->maxAttempts = $maxAttempts;

        if ($delay < 0) {
            throw new InvalidArgumentException(
                \sprintf('Delay must be greater than or equal to zero: `%s` given.', $delay)
            );
        }
        $this->delay = $delay;

        if ($multiplier < 1) {
            throw new InvalidArgumentException(
                \sprintf('Multiplier must be greater than zero: `%s` given.', $multiplier)
            );
        }
        $this->multiplier = $multiplier;
    }

    /**
     * @param positive-int|0 $attempts
     *
     * @return positive-int
     */
    public function getDelay(int $attempts = 0): int
    {
        return (int) \ceil($this->delay * $this->multiplier ** $attempts);
    }

    /**
     * @param positive-int|0 $attempts
     */
    public function isRetryable(\Throwable $exception, int $attempts = 0): bool
    {
        if ($exception instanceof JobException && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        if (!$exception instanceof RetryableExceptionInterface || $this->maxAttempts === 0) {
            return false;
        }

        return $exception->isRetryable() && $attempts < $this->maxAttempts;
    }
}
