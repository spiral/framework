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
        // If no prefix is specified, allow everything
        if ($prefix === null || $prefix === '') {
            return true;
        }

        // The tag must be at least prefix + 2 chars:
        //   1) The prefix itself
        //   2) The separator
        //   3) At least one more char after the separator
        if (\strlen($tag) < \strlen($prefix) + 2) {
            return false;
        }

        if (!\str_starts_with($tag, $prefix)) {
            return false;
        }

        return \in_array($tag[\strlen($prefix)], self::SEPARATOR, true);
    }

    public static function stripPrefix(string $tag, ?string $prefix): string
    {
        if (!self::hasPrefix($tag, $prefix)) {
            return $tag;
        }

        return \substr($tag, \strlen((string) $prefix) + 1);
    }
}
