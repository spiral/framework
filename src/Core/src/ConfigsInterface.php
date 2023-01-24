<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\ConfiguratorException;

/**
 * Provides array based configuration for specified config section. In addition configurator
 * interface is responsible for contextual config injections.
 *
 * @template TClass of object
 * @extends InjectorInterface<TClass>
 */
interface ConfigsInterface extends InjectorInterface
{
    /**
     * Return config for one specified section. Config has to be returned in component specific
     * array.
     *
     * @throws ConfiguratorException
     */
    public function getConfig(string $section = null): array;
}
