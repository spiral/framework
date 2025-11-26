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
     * @return int<0, max> Delay in seconds
     */
    public function getDelay(int $attempts = 0): int;
}
