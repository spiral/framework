<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

/**
 * Can replace string content.
 */
interface ReplaceableInterface
{
    /**
     * Replace sub string in element content.
     *
     * @param string|array $search
     * @param string|array $replace
     */
    public function replace($search, $replace);
}