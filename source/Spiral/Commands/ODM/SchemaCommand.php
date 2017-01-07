<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ODM;

use Spiral\Console\Command;
use Spiral\Debug\Benchmarker;
use Spiral\ODM\ODM;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs ODM schema update and binds SchemaBuilder in container.
 */
class SchemaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'odm:schema';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Update ODM schema';

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        ['indexes', 'i', InputOption::VALUE_NONE, 'Create requested database indexes']
    ];

    /**
     * @param Benchmarker $benchmarker
     * @param ODM         $odm
     */
    public function perform(Benchmarker $benchmarker, ODM $odm)
    {
        $benchmark = $benchmarker->benchmark($this, 'update');

        $builder = $odm->schemaBuilder(true);
        $odm->buildSchema($builder, true);

        $elapsed = number_format($benchmarker->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getSchemas());
        $this->write("<info>Schema have been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found documents: <comment>{$countModels}</comment></info>");

        if ($this->option('indexes')) {

            $benchmark = $benchmarker->benchmark($this, 'update');
            $builder->createIndexes();
            $elapsed = number_format($benchmarker->benchmark($this, $benchmark), 3);

            //todo: better language
            $this->writeln("<info>Index creation is done:</info> <comment>{$elapsed} s</comment>");
        } else {
            $this->writeln("<info>Silent mode on, no mongo indexes to be created.</info>");
        }
    }
}