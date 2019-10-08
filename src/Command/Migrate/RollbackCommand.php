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

final class RollbackCommand extends AbstractCommand
{
    const NAME        = 'migrate:rollback';
    const DESCRIPTION = 'Rollback one (default) or multiple migrations';
    const OPTIONS     = [
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
            $this->sprintf(
                "<info>Migration <comment>%s</comment> was successfully rolled back.</info>\n",
                $migration->getState()->getName()
            );
        }

        if (!$found) {
            $this->writeln("<fg=red>No executed migrations were found.</fg=red>");
        }
    }
}
