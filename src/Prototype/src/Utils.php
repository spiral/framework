<?php

declare(strict_types=1);

namespace Spiral\Prototype;

/**
 * @internal
 */
final class Utils
{
    public static function hasShortName(string $name): bool
    {
        return \mb_strpos($name, '\\') !== false;
    }

    /**
     * Create short name (without namespaces).
     */
    public static function shortName(string $name): string
    {
        $pos = \mb_strrpos($name, '\\');
        if ($pos === false) {
            return $name;
        }

        return \mb_substr($name, $pos + 1);
    }

    /**
     * Inject values to array at given index.
     */
    public static function injectValues(array $stmts, int $index, array $child): array
    {
        $before = \array_slice($stmts, 0, $index);
        $after = \array_slice($stmts, $index);

        return \array_merge($before, $child, $after);
    }

    /**
     * Remove trailing digits in the given name.
     */
    public static function trimTrailingDigits(string $name, int $number): string
    {
        $pos = \mb_strripos($name, (string)$number);
        if ($pos === false) {
            return $name;
        }

        return \mb_substr($name, 0, $pos);
    }
}
