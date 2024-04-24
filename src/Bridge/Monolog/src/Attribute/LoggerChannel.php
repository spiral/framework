<?php

declare(strict_types=1);

namespace Spiral\Monolog\Attribute;

use Psr\Log\LoggerInterface;

/**
 * Used to specify the channel name for the logger Monolog when it injected as {@see LoggerInterface} on auto-wiring.
 *
 * @see \Spiral\Monolog\LogFactory the injector that is required to support this attribute.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class LoggerChannel
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(public readonly string $name)
    {
    }
}
