<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Console\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Console component configuration.
 */
class ConsoleConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'console';

    /**
     * @var array
     */
    protected $config = [
        'updateSequence' => [],
        'configureSequence' => []
    ];

    /**
     * @return array
     */
    public function configureSequence()
    {
        return $this->config['configureSequence'];
    }

    /**
     * @return array
     */
    public function updateSequence()
    {
        return $this->config['updateSequence'];
    }
}