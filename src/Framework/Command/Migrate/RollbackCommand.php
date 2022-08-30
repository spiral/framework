<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Migrate;

use Symfony\Component\Console\Input\InputOption;

/**
 * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class RollbackCommand extends AbstractCommand
{
    protected const NAME        = 'migrate:rollback';
    protected const DESCRIPTION = 'Rollback one (default) or multiple migrations';
    protected const OPTIONS     = [
        ['all', 'a', InputOption::VALUE_NONE, 'Rollback all executed migrations'],
    ];

    /**
     * Perform command.
     */
    public function perform(): void
    {
        if (!$this->verifyEnvironment()) {
            //Making sure we can safely migrate in this environment
            return;
        }

        $this->migrator->configure();

        $found = false;
        $count = !$this->option('all') ? 1 : PHP_INT_MAX;
        while ($count > 0 && ($migration = $this->migrator->rollback())) {
            $found = true;
            $count--;
            $this->sprintf(
                "<info>Migration <comment>%s</comment> was successfully rolled back.</info>\n",
                $migration->getState()->getName()
            );
        }

        if (!$found) {
            $this->writeln('<fg=red>No executed migrations were found.</fg=red>');
        }
    }
}
