<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator;

/**
 * Example:
 * post.*
 * post.(save|delete)
 */
final class Matcher
{
    public function isPattern(string $string): bool
    {
        return strpos($string, '*') !== false || strpos($string, '|') !== false;
    }

    /**
     * Checks if string matches given pattern.
     */
    public function matches(string $string, string $pattern): bool
    {
        if ($string === $pattern) {
            return true;
        }
        if (!$this->isPattern($pattern)) {
            return false;
        }

        return (bool)preg_match($this->getRegex($pattern), $string);
    }

    private function getRegex(string $pattern): string
    {
        $regex = str_replace('*', '[a-z0-9_\-]+', addcslashes($pattern, '.-'));

        return "#^{$regex}$#i";
    }
}
