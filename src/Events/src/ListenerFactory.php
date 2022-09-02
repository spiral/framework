<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Core\Container;

final class ListenerFactory
{
    public function __construct(
        private readonly Container $container
    ) {
    }

    public function create(string|object $listener, string $method): \Closure
    {
        return function (object $event) use ($listener, $method) {
            if (\is_string($listener)) {
                $listener = $this->container->get($listener);
            }

            $this->container->invoke(
                [$listener, $method],
                ['event' => $event]
            );
        };
    }
}
