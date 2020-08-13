<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core\Traits\Config;

/**
 * Provides aliasing ability for config entities.
 *
 * @deprecated to be removed in future releases.
 */
trait AliasTrait
{
    /**
     * @param string $alias
     * @return string
     */
    public function resolveAlias(string $alias): string
    {
        while (is_string($alias) && isset($this->config) && isset($this->config['aliases'][$alias])) {
            $alias = $this->config['aliases'][$alias];
        }

        return $alias;
    }
}
