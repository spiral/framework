<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Doctrine\Common\Annotations\Annotation\Target;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Queue\Attribute\RetryPolicy;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class ExtendedRetryPolicy extends RetryPolicy
{
    public function __construct()
    {
        parent::__construct(maxAttempts: 10, delay: 12, multiplier: 5);
    }
}
