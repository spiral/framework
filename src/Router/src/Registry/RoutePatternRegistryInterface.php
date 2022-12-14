<?php

namespace Spiral\Router\Registry;

interface RoutePatternRegistryInterface
{
    public function register(string $name, string|\Stringable $pattern): void;

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function all(): array;
}
