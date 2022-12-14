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

    public function register(string $name, string|\Stringable $pattern): void
    {
        $this->patterns[$name] = (string)$pattern;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array
    {
        return $this->patterns;
    }
}
