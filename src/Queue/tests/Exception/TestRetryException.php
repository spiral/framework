<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Exception;

use Spiral\Queue\Exception\RetryableExceptionInterface;
use Spiral\Queue\RetryPolicyInterface;

final class TestRetryException extends \Exception implements RetryableExceptionInterface
{
    public function __construct(
        private readonly bool $retryable = true,
        private readonly ?RetryPolicyInterface $retryPolicy = null
    ) {
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function getRetryPolicy(): ?RetryPolicyInterface
    {
        return $this->retryPolicy;
    }
}
