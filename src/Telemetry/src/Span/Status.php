<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Span;

final class Status
{
    /**
     * @param non-empty-string $code
     * @param non-empty-string|null $description
     */
    public function __construct(
        public readonly string $code,
        public readonly ?string $description = null
    ) {
    }
}
