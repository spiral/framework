<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Represent list of connected modules.
 */
class ModulesConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'modules';

    /**
     * @var array
     */
    protected $config = [];
}