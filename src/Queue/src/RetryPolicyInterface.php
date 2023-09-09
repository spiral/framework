<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface RetryPolicyInterface
{
    /**
     * @param positive-int|0 $attempts
     */
    public function isRetryable(\Throwable $exception, int $attempts = 0): bool;

    /**
     * @param positive-int|0 $attempts
     *
     * @return positive-int
     */
    public function getDelay(int $attempts = 0): int;
}
