<?php

declare(strict_types=1);

if (!\function_exists('inject')) {
    /**
     * Macro function to be replaced by the injected value.
     */
    function inject(string $name, mixed $default = null): mixed
    {
        return $default;
    }
}

if (!\function_exists('injected')) {
    /**
     * Return true if block value has been defined.
     *
     * @psalm-suppress UnusedParam
     */
    function injected(string $name): bool
    {
        return false;
    }
}
