<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'migrate:rollback';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Rollback one (default) or multiple migrations';

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        ['all', 'a', InputOption::VALUE_NONE, 'Rollback all executed migrations']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        if (!$this->verifyConfigured() || !$this->verifyEnvironment()) {
            //Making sure we can safely migrate in this environment
            return;
        }

        $found = false;
        $count = !$this->option('all') ? 1 : PHP_INT_MAX;
        while ($count > 0 && ($migration = $this->migrator->rollback())) {
            $found = true;
            $count--;
            $this->writeln(interpolate(
                "<info>Migration <comment>{name}</comment> was successfully rolled back.</info>",
                ['name' => $migration->getState()->getName()]
            ));
        }

        if (!$found) {
            $this->writeln("<fg=red>No executed migrations were found.</fg=red>");
        }
    }
}