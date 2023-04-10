<?php

declare(strict_types=1);

namespace Spiral\Events\Config;

final class EventListener
{
    private const DEFAULT_METHOD = '__invoke';

    public string $method;

    /**
     * @param class-string $listener
     * @param ?non-empty-string $method
     * @param int<0, max> $priority
     */
    public function __construct(
        public string $listener,
        ?string $method = null,
        public int $priority = 0
    ) {
        $this->method = !empty($method) ? $method : self::DEFAULT_METHOD;
    }
}
