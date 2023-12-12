<?php

declare(strict_types=1);

namespace Spiral\Debug\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Debug\StateCollectorInterface;

/**
 * @psalm-type TCollector = StateCollectorInterface|string|Autowire<StateCollectorInterface>
 * @psalm-type TTag = non-empty-string|\Closure|\Stringable
 * @property array{
 *     collectors: array<TCollector>,
 *     tags: array<non-empty-string, TTag>
 * } $config
 */
final class DebugConfig extends InjectableConfig
{
    public const CONFIG = 'debug';

    protected array $config = [
        'collectors' => [],
        'tags' => [],
    ];

    /**
     * @return array<TCollector>
     */
    public function getCollectors(): array
    {
        return $this->config['collectors'];
    }

    /**
     * @return array<non-empty-string, TTag>
     */
    public function getTags(): array
    {
        return $this->config['tags'];
    }
}
