<?php

declare(strict_types=1);

namespace Spiral\Events\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Events\Processor\ProcessorInterface;

final class EventsConfig extends InjectableConfig
{
    public const CONFIG = 'events';

    protected array $config = [
        'processors' => [],
        'listeners' => [],
    ];

    /**
     * Get registered listeners.
     *
     * @psalm-return array{class-string: array<EventListener>}
     */
    public function getListeners(): array
    {
        $listeners = [];
        foreach ($this->config['listeners'] as $event => $eventListeners) {
            $listeners[$event] = \array_map(
                static fn (string|EventListener $listener): EventListener =>
                    \is_string($listener) ? new EventListener($listener) : $listener,
                $eventListeners
            );
        }

        return $listeners;
    }

    /**
     * @psalm-return array<ProcessorInterface|class-string|Autowire>
     */
    public function getProcessors(): array
    {
        return $this->config['processors'];
    }
}
