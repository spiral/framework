<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Simply forwards debug messages into various locations.
 */
final class NullLogger implements LoggerInterface
{
    use LoggerTrait;

    private readonly \Closure $receptor;

    public function __construct(
        callable $receptor,
        private readonly string $channel
    ) {
        $this->receptor = $receptor(...);
    }

    public function log(mixed $level, $message, array $context = []): void
    {
        \call_user_func($this->receptor, $this->channel, $level, $message, $context);
    }
}
