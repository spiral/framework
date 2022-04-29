<?php

declare(strict_types=1);

namespace Spiral\Http\Request;

/**
 * Access to server parameters of request, every requested key will be normalized for better
 * usability.
 */
final class ServerBag extends InputBag
{
    public function has(string $name): bool
    {
        return parent::has($this->normalize($name));
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return parent::get($this->normalize($name), $default);
    }

    public function fetch(array $keys, bool $fill = false, mixed $filler = null): array
    {
        $keys = \array_map(fn (string $name): string => $this->normalize($name), $keys);

        return parent::fetch($keys, $fill, $filler);
    }

    /**
     * Normalizing name to simplify selection.
     */
    protected function normalize(string $name): string
    {
        return \preg_replace('/[^a-z\.]/i', '_', \strtoupper($name));
    }
}
