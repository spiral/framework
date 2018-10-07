<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Migrations;

use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends AbstractCommand
{
    const NAME        = 'migrate:migrate';
    const DESCRIPTION = 'Perform one or all outstanding migrations';
    const OPTIONS     = [
        ['one', 'o', InputOption::VALUE_NONE, 'Execute only one (first) migration']
    ];

    /**
     * Execute one or multiple migrations.
     */
    public function perform()
    {
        if (!$this->verifyConfigured() || !$this->verifyEnvironment()) {
            return;
        }

        $found = false;
        $count = $this->option('one') ? 1 : PHP_INT_MAX;

        while ($count > 0 && ($migration = $this->migrator->run())) {
            $found = true;
            $count--;

            $this->sprintf(
                "<info>Migration <comment>%s</comment> was successfully executed.</info>",
                $migration->getState()->getName()
            );
        }

        if (!$found) {
            $this->writeln("<fg=red>No outstanding migrations were found.</fg=red>");
        }
    }
}