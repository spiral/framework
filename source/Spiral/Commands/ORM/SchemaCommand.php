<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ORM;

use Spiral\Console\Command;
use Spiral\Debug\Benchmarker;
use Spiral\ORM\ORM;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs ODM schema update and binds SchemaBuilder in container.
 */
class SchemaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'orm:schema';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Update ORM schema';

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        [
            'alter',
            'a',
            InputOption::VALUE_NONE,
            'Automatically alter databases based on declared schemas'
        ],
        //todo: drop external
    ];

    /**
     * @param Benchmarker $benchmarker
     * @param ORM         $orm
     */
    public function perform(Benchmarker $benchmarker, ORM $orm)
    {
        $benchmark = $benchmarker->benchmark($this, 'update');

        $builder = $orm->schemaBuilder(true);

        //Rendering schema
        $orm->buildSchema($builder->renderSchema(), true);

        $elapsed = number_format($benchmarker->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getSchemas());
        $this->write("<info>ORM Schema have been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found records: <comment>{$countModels}</comment></info>");

        if ($this->option('alter')) {
            $benchmark = $benchmarker->benchmark($this, 'update');
            $builder->pushSchema();
            $elapsed = number_format($benchmarker->benchmark($this, $benchmark), 3);

            $this->writeln("<info>Databases have been modified:</info> <comment>{$elapsed} s</comment>");
        } else {
            foreach ($builder->getTables() as $table) {
                if ($table->getComparator()->hasChanges()) {
                    $this->writeln(
                        "<fg=cyan>Table schema '<comment>{$table}</comment>' has changes.</fg=cyan>"
                    );
                }
            }

            $this->writeln("<info>Silent mode on, no databases altered.</info>");
        }
    }
}