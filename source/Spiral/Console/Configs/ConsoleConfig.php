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
        'locateCommands'    => true,
        'commands'          => [],
        'updateSequence'    => [],
        'configureSequence' => []
    ];

    /**
     * Indication that ConsoleDispatcher must locate commands.
     *
     * @return bool
     */
    public function locateCommands(): bool
    {
        if (!array_key_exists('locateCommands', $this->config)) {
            //Legacy config support
            return true;
        }

        return $this->config['locateCommands'];
    }

    /**
     * User defined set of commands (to be used when auto-location is off).
     *
     * @return array
     */
    public function userCommands(): array
    {
        if (!array_key_exists('commands', $this->config)) {
            //Legacy config support
            return [];
        }

        return $this->config['commands'];
    }

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