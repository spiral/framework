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
        'updateSequence'    => [],
        'configureSequence' => []
    ];

    /**
     * Set of commands to be executed in "spiral:configure" command.
     *
     * @return array
     */
    public function configureSequence(): array
    {
        return $this->config['configureSequence'];
    }

    /**
     * Set of commands to be executed in "spiral:update" command.
     *
     * @return array
     */
    public function updateSequence(): array
    {
        return $this->config['updateSequence'];
    }
}