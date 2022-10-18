<?php

declare(strict_types=1);

namespace Spiral\Events\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Events\Processor\ProcessorInterface;

/**
 * @psalm-type TProcessor = ProcessorInterface|class-string<ProcessorInterface>|Autowire<ProcessorInterface>
 * @psalm-type TListener = class-string|EventListener
 * @psalm-type TInterceptor = class-string<CoreInterceptorInterface>|CoreInterceptorInterface|Autowire<CoreInterceptorInterface>
 * @property array{
 *     processors: TProcessor[],
 *     listeners: array<class-string, TListener[]>,
 *     interceptors: TInterceptor[]
 * } $config
 */
final class EventsConfig extends InjectableConfig
{
    public const CONFIG = 'events';

    protected array $config = [
        'processors' => [],
        'listeners' => [],
        'interceptors' => [],
    ];

    /**
     * Get registered listeners.
     *
     * @return array<class-string, EventListener[]>
     */
    public function getListeners(): array
    {
        $listeners = [];
        foreach ($this->config['listeners'] as $event => $eventListeners) {
            $listeners[$event] = \array_map(
                self::normalizeListener(...),
                $eventListeners
            );
        }

        return $listeners;
    }

    /**
     * @return TProcessor[]
     */
    public function getProcessors(): array
    {
        return $this->config['processors'];
    }

    /**
     * @return TInterceptor[]
     */
    public function getInterceptors(): array
    {
        return $this->config['interceptors'];
    }

    /**
     * @param TListener $listener
     */
    private static function normalizeListener(string|EventListener $listener): EventListener
    {
        return \is_string($listener) ? new EventListener($listener) : $listener;
    }
}
