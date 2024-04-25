<?php

declare(strict_types=1);

namespace Spiral\Logger\Attribute;

use Psr\Log\LoggerInterface;

/**
 * Used to specify the channel name for the logger when it injected as {@see LoggerInterface} on auto-wiring.
 *
 * Note: {@see \Spiral\Logger\LoggerInjector} should be registered in the container to support this attribute.
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
