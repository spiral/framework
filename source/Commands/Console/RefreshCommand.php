<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Console;

use Spiral\Components\Console\Command;

class RefreshCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'console:refresh';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Reindex console commands.';

    /**
     * Refresh commands cache.
     */
    public function perform()
    {
        $this->console->findCommands();
        $commands = count($this->console->getCommands());

        $this->writeln(
            "Console commands re-indexed, <comment>{$commands}</comment> commands found."
        );
    }
}