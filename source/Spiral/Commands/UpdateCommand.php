<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands;

use Spiral\Console\Command;

/**
 * Execute sequence of commands declared on console component configuration. Usually used to run
 * migrations, update orm and odm schemas and etc.
 */
class UpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'update';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run Spiral Development server on specified host and port.';

    /**
     * Perform command.
     */
    public function perform()
    {
        foreach ($this->console->config()['updateSequence'] as $command => $options) {
            $this->console->command($command, $options, $this->output);
        }
    }
}