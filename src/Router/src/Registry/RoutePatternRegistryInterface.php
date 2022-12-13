<?php

namespace Spiral\Router\Registry;

interface RoutePatternRegistryInterface
{
    public function register(string $name, string|\Stringable $pattern): void;

    /**
     * @return array<string, string>
     */
    public function all(): array;
}
