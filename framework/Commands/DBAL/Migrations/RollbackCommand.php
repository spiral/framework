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

class RollbackCommand extends BaseCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'migrate:rollback';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Rollback one (default) or multiple migrations.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['all', 'a', InputOption::VALUE_NONE, 'Rollback all executed migrations.']
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

        $found = false;
        $count = !$this->option('all') ? 1 : PHP_INT_MAX;
        while ($count && ($migration = $this->getMigrator()->rollback()))
        {
            $found = true;
            $count--;
            $this->writeln(
                "<info>Migration <comment>{$migration['name']}</comment> "
                . "was successfully rolled back.</info>"
            );
        }

        if (!$found)
        {
            $this->writeln("<fg=red>No executed migrations were found.</fg=red>");
        }
    }
}