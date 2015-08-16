<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Database\Exceptions\MigratorException;
use Spiral\Database\Migrations\Migrator;
use Spiral\Reactor\Generators\MigrationGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create and register migration class.
 */
class MigrationCommand extends AbstractCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = MigrationGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'migration';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:migration';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new migration.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Migration name.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor  $reactor
     * @param Migrator $migrator
     */
    public function perform(Reactor $reactor, Migrator $migrator)
    {
        /**
         * @var MigrationGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        foreach ($this->option('create') as $table) {
            $generator->createTable($table);
        }

        foreach ($this->option('alter') as $table) {
            $generator->alterTable($table);
        }

        //Generating
        $generator->render();

        //We have to make sure that class were loaded
        include_once($generator->getFilename());

        //Registering migration in migrator
        try {
            $filename = $migrator->registerMigration(
                $this->argument('name'),
                $generator->getClassName()
            );
        } catch (MigratorException $exception) {
            $this->writeln("<fg=red>{$exception->getMessage()}/fg=red>");

            return;
        } finally {
            //We don't need old class anymore
            $this->files->delete($generator->getFilename());
        }

        if (empty($filename)) {
            $this->writeln(
                "<comment>Migration already exists:</comment> {$generator->getClassName()}"
            );

            return;
        }

        $this->writeln("<info>Migration was successfully created:</info> {$filename}");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'create',
                'c',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Create table(s) creation/dropping code.'
            ],
            [
                'alter',
                'a',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Create table(s) altering code.'
            ],
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ]
        ];
    }
}