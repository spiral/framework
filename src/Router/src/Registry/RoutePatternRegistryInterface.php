<?php

declare(strict_types=1);

namespace Spiral\Router\Registry;

interface RoutePatternRegistryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-string|\Stringable $pattern
     */
    public function register(string $name, string|\Stringable $pattern): void;

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array;
}
