<?php

declare(strict_types=1);

namespace Spiral\Queue\Exception;

use Spiral\Queue\RetryPolicyInterface;

interface RetryableExceptionInterface
{
    public function isRetryable(): bool;

    public function getRetryPolicy(): ?RetryPolicyInterface;
}
