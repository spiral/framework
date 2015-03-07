<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Components\Console\Command;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Migrations\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'create:migration';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration class.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments() method.
     *
     * @var array
     */
    protected $arguments = array(
        ['name', InputArgument::REQUIRED, 'Migration name.']
    );

    /**
     * Render and register new migration file.
     */
    public function perform()
    {
        $repository = $this->dbal->migrationRepository();
        $filename = $repository->getFilename($this->argument('name'), '', true);

        //Migration creator
        $creator = Creators\MigrationCreator::make(array('name' => $this->argument('name')));

        foreach ($this->option('create') as $table)
        {
            $creator->createTable($table);
        }

        foreach ($this->option('alter') as $table)
        {
            $creator->alterTable($table);
        }

        $this->option('comment') && $creator->class->setComment($this->option('comment'));

        $creator->render($filename);
        $this->writeln("<info>Migration successfully created:</info> " . basename($filename));
    }

    /**
     * Command options. By default "options" property will be used.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            ['create', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Create table(s) creation/dropping code.'],
            ['alter', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Create table(s) altering code.'],
            ['comment', null, InputOption::VALUE_OPTIONAL, 'Optional comment to add as class header.']
        );
    }
}