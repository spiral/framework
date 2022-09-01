<?php

declare(strict_types=1);

namespace Spiral\Events\Config;

final class EventListener
{
    private const DEFAULT_METHOD = '__invoke';

    public string $method;

    public function __construct(
        public string $listener,
        public ?string $event = null,
        ?string $method = null,
        public int $priority = 0
    ) {
        $this->method = !empty($method) ? $method : self::DEFAULT_METHOD;
    }
}
