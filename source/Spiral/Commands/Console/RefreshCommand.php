<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)

 */
namespace Spiral\Commands\Console;

use Spiral\Console\Command;

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
    protected $description = 'Reindex console commands.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $commands = count($this->console->findCommands());

        $this->writeln(
            "Console commands re-indexed, <comment>{$commands}</comment> commands found."
        );
    }
}