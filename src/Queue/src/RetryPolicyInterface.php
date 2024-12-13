<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface RetryPolicyInterface
{
    /**
     * @param int<0, max> $attempts
     */
    public function isRetryable(\Throwable $exception, int $attempts = 0): bool;

    /**
     * @param int<0, max> $attempts
     *
     * @return positive-int
     */
    public function getDelay(int $attempts = 0): int;
}
