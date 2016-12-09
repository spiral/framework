<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ORM;

use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Spiral\Console\Command;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Debugger;
use Spiral\ORM\ORM;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs ORM schema update and binds schema builder in container.
 */
class SchemaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'orm:schema';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update ORM schema';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['sync', null, InputOption::VALUE_NONE, 'Syncronize database schemas']
    ];

    /**
     * @param Debugger           $debugger
     * @param ORM                $orm
     * @param ContainerInterface $container
     * @param ClassLocator       $locator
     */
    public function perform(
        Debugger $debugger,
        ORM $orm,
        ContainerInterface $container,
        ClassLocator $locator
    ) {
        //We don't really need location errors here
        $locator->setLogger(new NullLogger());

        $benchmark = $debugger->benchmark($this, 'update');
        $builder = $orm->schemaBuilder($locator);

        //To make builder available for other commands (in sequence)
        $container->bind(get_class($builder), $builder);

        if (!$this->option('sync')) {
            $this->writeln("<comment>Silent mode on, no databases to be altered.</comment>");
        }

        $orm->updateSchema($builder, $this->option('sync'));

        $elapsed = number_format($debugger->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getRecords());
        $this->write("<info>ORM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found records: <comment>{$countModels}</comment></info>");
    }
}