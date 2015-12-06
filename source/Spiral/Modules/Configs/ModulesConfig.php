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
 * Modules config.
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
    protected $config = [
        //Config content automatically populated on module registration
    ];

    /**
     * Get list of already registered modules.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->config;
    }
}