<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Console;

use Spiral\Console\Command;
use Spiral\Console\ConsoleDispatcher;

/**
 * Re-index available console commands.
 */
class RefreshCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'console:refresh';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Re-index console commands.';

    /**
     * @param ConsoleDispatcher $dispatcher
     */
    public function perform(ConsoleDispatcher $dispatcher)
    {
        $commands = count($dispatcher->locateCommands());
        $this->writeln("Console commands re-indexed, <comment>{$commands}</comment> commands found.");
    }
}