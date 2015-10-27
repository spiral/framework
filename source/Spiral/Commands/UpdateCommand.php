<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
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
    protected $description = 'Perform application schemas and cache update.';

    /**
     * Perform command.
     */
    public function perform()
    {
        foreach ($this->console->config()['updateSequence'] as $command => $options) {
            if (!empty($options['header'])) {
                $this->writeln($options['header']);
            }
            $this->console->command($command, $options['options'], $this->output);
            if (!empty($options['footer'])) {
                $this->writeln($options['footer']);
            }
        }
    }
}