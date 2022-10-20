<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Span;

/**
 * @internal
 */
final class Status
{
    /**
     * @param non-empty-string|int $code
     * @param non-empty-string|null $description
     */
    public function __construct(
        public readonly string|int $code,
        public readonly ?string $description = null
    ) {
    }
}
