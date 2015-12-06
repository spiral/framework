<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

/**
 * Provides ability to safely edited content of existed configurations. Attention, do not miss with
 * Core\ConfiguratorInterface.
 */
interface ConfiguratorInterface
{
    public function configure($class, $config, $placeholder, $value);
}