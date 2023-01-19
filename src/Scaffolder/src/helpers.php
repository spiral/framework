<?php

declare(strict_types=1);

namespace Spiral\Scaffolder;

if (!\function_exists('trimPostfix')) {
    /**
     * @internal
     */
    function trimPostfix(string $name, string $postfix): string
    {
        $pos = \mb_strripos($name, $postfix);

        return $pos === false ? $name : \mb_substr($name, 0, $pos);
    }
}

if (!\function_exists('defineArrayType')) {
    /**
     * @internal
     */
    function defineArrayType(array $array, string $failureType = null): ?string
    {
        $types = \array_map(static fn ($value): string => \gettype($value), $array);

        $types = \array_unique($types);

        return \count($types) === 1 ? $types[0] : $failureType;
    }
}
