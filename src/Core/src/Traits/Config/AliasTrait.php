<?php

declare(strict_types=1);

namespace Spiral\Core\Traits\Config;

use Spiral\Core\Exception\Container\ContainerException;

/**
 * Provides aliasing ability for config entities.
 *
 * @deprecated to be removed in future releases.
 */
trait AliasTrait
{
    public function resolveAlias(string $alias): string
    {
        $antiCircleReference = [];
        while (isset($this->config, $this->config['aliases'][$alias]) && \is_string($alias)) {
            if (\in_array($alias, $antiCircleReference, true)) {
                throw new ContainerException(\sprintf('Circle reference detected for alias `%s`.', $alias));
            }
            $antiCircleReference[] = $alias;

            $alias = $this->config['aliases'][$alias];
        }

        return $alias;
    }
}
