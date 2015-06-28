<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\DBAL\Migrations;

use Symfony\Component\Console\Input\InputOption;

class ReplayCommand extends BaseCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'migrate:replay';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Replay (down, up) one or multiple migrations.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['all', 'a', InputOption::VALUE_NONE, 'Replay all migrations.']
    );

    /**
     * Performing one or multiple migrations.
     */
    public function perform()
    {
        if (!$this->checkEnvironment())
        {
            return;
        }

        $rollback = array('--database' => $this->option('database'), '--safe' => true);
        $migrate = array('--database' => $this->option('database'), '--safe' => true);

        if ($this->option('all'))
        {
            $rollback['--all'] = true;
        }
        else
        {
            $migrate['--one'] = true;
        }

        $this->writeln("Rolling back executed migration(s).");
        $this->console->command('migrate:rollback', $rollback, $this->output);
        $this->writeln("");
        $this->writeln("Executing outstanding migration(s).");
        $this->console->command('migrate', $migrate, $this->output);
    }
}