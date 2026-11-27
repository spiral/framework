<?php

declare(strict_types=1);

namespace Spiral\Filters\Config;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Interceptors\InterceptorInterface;

final class FiltersConfig extends InjectableConfig
{
    public const CONFIG = 'filters';

    protected array $config = [
        'interceptors' => [],
        'maxNestedFilters' => 10_000,
        'maxNestedKeyLength' => 1024,
    ];

    /**
     * @return array<class-string<CoreInterceptorInterface|InterceptorInterface>>
     */
    public function getInterceptors(): array
    {
        return (array) ($this->config['interceptors'] ?? []);
    }

    /**
     * How many nested filters one root filter may build across all nesting levels.
     *
     * @return positive-int|null Null disables the limit.
     */
    public function getMaxNestedFilters(): ?int
    {
        return $this->positiveIntOrNull('maxNestedFilters');
    }

    /**
     * How long, in bytes, a key of a {@see \Spiral\Filters\Attribute\NestedArray} element may be.
     * The key becomes part of the input prefix of every filter below it.
     *
     * @return positive-int|null Null disables the limit.
     */
    public function getMaxNestedKeyLength(): ?int
    {
        return $this->positiveIntOrNull('maxNestedKeyLength');
    }

    private function positiveIntOrNull(string $key): ?int
    {
        $value = $this->config[$key] ?? null;

        return \is_int($value) && $value > 0 ? $value : null;
    }
}
