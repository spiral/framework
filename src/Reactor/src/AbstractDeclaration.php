<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor;

/**
 * Generic element declaration.
 */
abstract class AbstractDeclaration implements DeclarationInterface
{
    /**
     * Access level constants.
     */
    public const ACCESS_PUBLIC    = 'public';
    public const ACCESS_PROTECTED = 'protected';
    public const ACCESS_PRIVATE   = 'private';

    /**
     * @param string $string
     * @param int    $indent
     * @return string
     */
    protected function addIndent(string $string, int $indent = 0): string
    {
        return str_repeat(self::INDENT, max($indent, 0)) . $string;
    }
}
