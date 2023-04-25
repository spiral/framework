<?php

namespace Spiral\Router\Registry;

final class DefaultPatternRegistry implements RoutePatternRegistryInterface
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $patterns = [
        'int' => '\d+',
        'integer' => '\d+',
        'uuid' => '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}',
    ];

    /**
     * @param non-empty-string $name
     * @param non-empty-string|\Stringable $pattern
     */
    public function register(string $name, string|\Stringable $pattern): void
    {
        $pattern = (string) $pattern;
        \assert($pattern !== '');
        $this->patterns[$name] = $pattern;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array
    {
        return $this->patterns;
    }
}
