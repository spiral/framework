<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Replay (down, up) one or multiple migrations.
 */
class ReplayCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'migrate:replay';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Replay (down, up) one or multiple migrations.';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['all', 'a', InputOption::VALUE_NONE, 'Replay all migrations.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        if (!$this->verifyEnvironment()) {
            //Making sure we can safely migrate in this environment
            return;
        }

        $rollback = ['--safe' => true];
        $migrate = ['--safe' => true];

        if ($this->option('all')) {
            $rollback['--all'] = true;
        } else {
            $migrate['--one'] = true;
        }

        $this->writeln("Rolling back executed migration(s).");
        $this->console->command('migrate:rollback', $rollback, $this->output);

        $this->writeln("");

        $this->writeln("Executing outstanding migration(s).");
        $this->console->command('migrate', $migrate, $this->output);
    }
}