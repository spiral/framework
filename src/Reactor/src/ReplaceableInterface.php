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
 * Provides ability to replace strings within the content and all inner declarations.
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
