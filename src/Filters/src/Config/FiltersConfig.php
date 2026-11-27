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
    private const LIMITS = [
        'maxNestedFilters' => 2000,
        'maxNestedKeyLength' => 1024,
    ];

    protected array $config = [
        'interceptors' => [],
        ...self::LIMITS,
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
     * @return positive-int|null Null when the limit is disabled with `false` in the config.
     *
     * @throws ConfigException
     */
    public function getMaxNestedFilters(): ?int
    {
        return $this->limit('maxNestedFilters');
    }

    /**
     * How long, in bytes, a key of a {@see \Spiral\Filters\Attribute\NestedArray} element may be.
     * The key becomes part of the input prefix of every filter below it.
     *
     * @return positive-int|null Null when the limit is disabled with `false` in the config.
     *
     * @throws ConfigException
     */
    public function getMaxNestedKeyLength(): ?int
    {
        return $this->limit('maxNestedKeyLength');
    }

    /**
     * Only an explicit `false` disables a limit. Null keeps the default, so an unset environment
     * variable does not turn the protection off; a string of digits is accepted, since that is how
     * a set one arrives.
     *
     * @param key-of<self::LIMITS> $key
     *
     * @return positive-int|null
     *
     * @throws ConfigException When the value is neither false, null nor a positive integer: a
     *         mistyped limit must not turn the protection off.
     */
    private function limit(string $key): ?int
    {
        $value = $this->config[$key] ?? self::LIMITS[$key];
        if ($value === false) {
            return null;
        }

        // 18 digits always fit into int, so the cast cannot overflow into a float.
        if (\is_string($value) && \preg_match('/^[1-9]\d{0,17}$/D', $value) === 1) {
            $value = (int) $value;
        }

        if (!\is_int($value) || $value < 1) {
            throw new ConfigException(\sprintf(
                'Filters config option `%s` must be a positive integer, null for the default or false to disable it, %s given.',
                $key,
                \is_scalar($value) ? \var_export($value, true) : \get_debug_type($value),
            ));
        }

        return $value;
    }
}
