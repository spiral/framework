<?php

declare(strict_types=1);

namespace Spiral\Filters\Config;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\Exception\ConfigException;
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
     *
     * @throws ConfigException
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
     *
     * @throws ConfigException
     */
    public function getMaxNestedKeyLength(): ?int
    {
        return $this->positiveIntOrNull('maxNestedKeyLength');
    }

    /**
     * A string of digits is accepted as well, since that is how a limit read from the environment arrives.
     *
     * @return positive-int|null
     *
     * @throws ConfigException When the value is neither null nor a positive integer: a mistyped limit
     *         must not turn the protection off.
     */
    private function positiveIntOrNull(string $key): ?int
    {
        $value = $this->config[$key] ?? null;
        if ($value === null) {
            return null;
        }

        // 18 digits always fit into int, so the cast cannot overflow into a float.
        if (\is_string($value) && \preg_match('/^[1-9]\d{0,17}$/D', $value) === 1) {
            $value = (int) $value;
        }

        if (!\is_int($value) || $value < 1) {
            throw new ConfigException(\sprintf(
                'Filters config option `%s` must be a positive integer or null, %s given.',
                $key,
                \is_scalar($value) ? \var_export($value, true) : \get_debug_type($value),
            ));
        }

        return $value;
    }
}
