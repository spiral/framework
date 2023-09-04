<?php

declare(strict_types=1);

namespace Spiral\Queue\Attribute;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Queue\RetryPolicyInterface;
use Spiral\Queue\RetryPolicy as Policy;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("maxAttempts", type="int"),
 *     @Attribute("delay", type="int"),
 *     @Attribute("multiplier", type="float"),
 * })
 */
#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class RetryPolicy
{
    /**
     * @param 0|positive-int $maxAttempts
     * @param positive-int $delay in seconds.
     */
    public function __construct(
        private readonly int $maxAttempts = 3,
        private readonly int $delay = 1,
        private readonly float $multiplier = 1
    ) {
    }

    public function getRetryPolicy(): RetryPolicyInterface
    {
        return new Policy(
            maxAttempts: $this->maxAttempts,
            delay: $this->delay,
            multiplier: $this->multiplier
        );
    }
}
