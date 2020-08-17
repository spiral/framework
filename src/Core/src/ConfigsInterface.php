<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\ConfiguratorException;

/**
 * Provides array based configuration for specified config section. In addition configurator
 * interface is responsible for contextual config injections.
 */
interface ConfigsInterface extends InjectorInterface
{
    /**
     * Return config for one specified section. Config has to be returned in component specific
     * array.
     *
     * @param string $section
     * @return array
     *
     * @throws ConfiguratorException
     */
    public function getConfig(string $section = null): array;
}
