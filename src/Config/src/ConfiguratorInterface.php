<?php

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\ConfigDeliveredException;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Exception\ConfiguratorException;

/**
 * Provides ability to modify configs values in runtime.
 *
 * @template TClass of object
 * @extends ConfigsInterface<TClass>
 */
interface ConfiguratorInterface extends ConfigsInterface
{
    /**
     * Check if configuration sections exists or defined as default.
     */
    public function exists(string $section): bool;

    /**
     * Set default value for configuration section. Default values will be overwritten by user specified config
     * on first level only. Only one default value is allowed per configuration section, use modify method in order
     * to alter existed content.  Must throw `PatchDeliveredException` config has already been delivered and strict
     * mode is enabled.
     *
     * Example:
     * >> default
     * {
     *      "key": ["value", "value2"]
     * }
     *
     * >> user defined
     * {
     *      "key": ["value3"]
     * }
     *
     * >> result
     * {
     *      "key": ["value3"]
     * }
     *
     * @throws ConfiguratorException
     * @throws ConfigDeliveredException
     */
    public function setDefaults(string $section, array $data): void;

    /**
     * Modifies selected config section. Must throw `PatchDeliveredException` if modification is
     * not allowed due config has already been delivered.
     *
     * @throws ConfiguratorException
     * @throws ConfigDeliveredException
     */
    public function modify(string $section, PatchInterface $patch): array;
}
