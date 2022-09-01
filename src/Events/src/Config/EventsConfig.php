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
     * @return EventListener[]
     */
    public function getListeners(): array
    {
        return \array_map(static function (string|EventListener $listener): EventListener {
            if ($listener instanceof EventListener) {
                return $listener;
            }

            return new EventListener($listener);
        }, $this->config['listeners']);
    }

    /**
     * @psalm-return array<ProcessorInterface|class-string|Autowire>
     */
    public function getProcessors(): array
    {
        return $this->config['processors'];
    }
}
