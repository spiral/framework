<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Import;

final class TagHelper
{
    private const SEPARATOR = [':', '.', '/'];

    /**
     * Validate tag against namespace.
     *
     * Example:
     *  - foo:bar
     *  - foo.bar
     *  - foo/bar
     */
    public static function hasPrefix(string $tag, ?string $prefix): bool
    {
        if ($prefix === null) {
            return true;
        }

        if (!\str_starts_with($tag, $prefix)) {
            return false;
        }

        if (!\in_array($tag[\strlen($prefix)], self::SEPARATOR, true)) {
            return false;
        }

        return true;
    }

    public static function stripPrefix(string $tag, ?string $prefix): string
    {
        if (!self::hasPrefix($tag, $prefix)) {
            return $tag;
        }

        return \substr($tag, \strlen((string) $prefix) + 1);
    }
}