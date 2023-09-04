<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\Attribute\RetryPolicy;

#[RetryPolicy]
final class WithDefaultRetryPolicyAttribute
{
}
