<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

/**
 * Used to wrap the resolving context to force Proxy Fallback Factory.
 *
 * @internal
 */
final class RetryContext implements \Stringable
{
    /**
     * @param \Stringable|string|null $context Original context.
     */
    public function __construct(
        public \Stringable|string|null $context = null,
    ) {}

    public function __toString(): string
    {
        return (string) $this->context;
    }
}
