<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Console\Configs\ConsoleConfig;
use Spiral\Console\ConsoleDispatcher;

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
    protected $description = 'Application schemas and cache update';

    /**
     * @param ConsoleConfig     $config
     * @param ConsoleDispatcher $dispatcher
     */
    public function perform(ConsoleConfig $config, ConsoleDispatcher $dispatcher)
    {
        foreach ($config->updateSequence() as $command => $options) {
            if (!empty($options['header'])) {
                $this->writeln($options['header']);
            }

            $dispatcher->command($command, $options['options'], $this->output);

            if (!empty($options['footer'])) {
                $this->writeln($options['footer']);
            }
        }
    }
}