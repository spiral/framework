<?php

declare(strict_types=1);

namespace Spiral\Events;

interface ListenerFactoryInterface
{
    /**
     * @param class-string|object $listener
     * @return \Closure(object $event): void
     * @throws \BadMethodCallException
     */
    public function create(string|object $listener, string $method): \Closure;
}
