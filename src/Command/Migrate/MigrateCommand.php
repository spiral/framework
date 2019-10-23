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

final class MigrateCommand extends AbstractCommand
{
    protected const NAME        = 'migrate';
    protected const DESCRIPTION = 'Perform one or all outstanding migrations';
    protected const OPTIONS     = [
        ['one', 'o', InputOption::VALUE_NONE, 'Execute only one (first) migration']
    ];

    /**
     * Execute one or multiple migrations.
     */
    public function perform(): void
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
                "<info>Migration <comment>%s</comment> was successfully executed.</info>\n",
                $migration->getState()->getName()
            );
        }

        if (!$found) {
            $this->writeln('<fg=red>No outstanding migrations were found.</fg=red>');
        }
    }
}