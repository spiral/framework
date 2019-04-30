<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Migrate;

use Spiral\Console\ConsoleCore;
use Symfony\Component\Console\Input\InputOption;

class ReplayCommand extends AbstractCommand
{
    const NAME        = 'migrate:replay';
    const DESCRIPTION = 'Replay (down, up) one or multiple migrations';
    const OPTIONS     = [
        ['all', 'a', InputOption::VALUE_NONE, 'Replay all migrations.']
    ];

    /**
     * @param ConsoleCore $console
     * @throws \Throwable
     */
    public function perform(ConsoleCore $console)
    {
        if (!$this->verifyEnvironment()) {
            //Making sure we can safely migrate in this environment
            return;
        }

        $rollback = ['--force' => true];
        $migrate = ['--force' => true];

        if ($this->option('all')) {
            $rollback['--all'] = true;
        } else {
            $migrate['--one'] = true;
        }

        $this->writeln("Rolling back executed migration(s)...");
        $console->run('migrate:rollback', $rollback, $this->output);

        $this->writeln("");

        $this->writeln("Executing outstanding migration(s)...");
        $console->run('migrate', $migrate, $this->output);
    }
}