<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\Attribute\RetryPolicy;

#[RetryPolicy(maxAttempts: 5, delay: 3_000, multiplier: 2.5)]
final class WithRetryPolicyAttribute {}
