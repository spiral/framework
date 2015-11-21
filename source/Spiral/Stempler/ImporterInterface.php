<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

/**
 * ImportInterface used by Stempler to define what tags should be treated as includes and how to
 * resolve their view or namespace.
 */
interface ImporterInterface
{
    /**
     * Check if element (tag) has to be imported.
     *
     * @param string $element Element name.
     * @param array  $token   Context token.
     * @return bool
     */
    public function importable($element, array $token);

    /**
     * Get imported element location. Must be supported by Stempler implementation.
     *
     * @param string $element Element name.
     * @param array  $token   Context token.
     * @return mixed
     */
    public function resolvePath($element, array $token);
}