<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ODM;

use Psr\Log\NullLogger;
use Spiral\Console\Command;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Debugger;
use Spiral\ODM\ODM;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs ODM schema update and binds SchemaBuilder in container.
 */
class SchemaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'odm:schema';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update ODM schema';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['silent', 's', InputOption::VALUE_NONE, 'Do not alter database']
    ];

    /**
     * @param Debugger           $debugger
     * @param ODM                $odm
     * @param ContainerInterface $container
     * @param ClassLocator       $locator
     */
    public function perform(
        Debugger $debugger,
        ODM $odm,
        ContainerInterface $container,
        ClassLocator $locator
    ) {
        //We don't really need location errors here
        $locator->setLogger(new NullLogger());

        $benchmark = $debugger->benchmark($this, 'update');
        $builder = $odm->schemaBuilder($locator);

        //To make builder available for other commands (in sequence)
        $container->bind(get_class($builder), $builder);

        if ($this->option('silent')) {
            $this->writeln("<comment>Silent mode on, no databases to be altered.</comment>");
        }

        //Save and syncronize
        $odm->updateSchema($builder, !$this->option('silent'));

        $elapsed = number_format($debugger->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getDocuments());
        $this->write("<info>ORM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found documents: <comment>{$countModels}</comment></info>");
    }
}