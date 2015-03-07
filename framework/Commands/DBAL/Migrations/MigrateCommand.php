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

class MigrateCommand extends BaseCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'migrate';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Perform one or all outstanding migrations.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions() method.
     *
     * @var array
     */
    protected $options = array(
        ['one', 'o', InputOption::VALUE_NONE, 'Execute only one (first) migration.']
    );

    /**
     * Execute one or multiple migrations.
     */
    public function perform()
    {
        if (!$this->checkEnvironment())
        {
            return;
        }

        $found = false;
        $count = $this->option('one') ? 1 : PHP_INT_MAX;
        while ($count && ($migration = $this->getMigrator()->run()))
        {
            $found = true;
            $count--;
            $this->writeln("<info>Migration <comment>{$migration['name']}</comment> was successfully executed.</info>");
        }

        if (!$found)
        {
            $this->writeln("<fg=red>No outstanding migrations were found.</fg=red>");
        }
    }
}