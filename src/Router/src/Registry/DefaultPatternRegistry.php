<?php

namespace Spiral\Router\Registry;

final class DefaultPatternRegistry implements RoutePatternRegistryInterface
{
    private array $patterns = [];

    public function __construct(array $defaultPatterns = [])
    {
        foreach ($defaultPatterns as $name => $pattern) {
            $this->register($name, $pattern);
        }
    }

    public function register(string $name, string|\Stringable $pattern): void
    {
        if (!isset($this->patterns[$name])) {
            $this->patterns[$name] = (string)$pattern;
        }
    }

    public function all(): array
    {
        return $this->patterns;
    }
}
