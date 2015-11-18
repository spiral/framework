<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ODM;

use Spiral\Console\Command;

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
    protected $description = 'Update ODM schema.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $benchmark = $this->debugger->benchmark($this, 'update');
        $builder = $this->odm->updateSchema();
        $this->container->bind(get_class($builder), $builder);
        $elapsed = number_format($this->debugger->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getDocuments());
        $this->write("<info>ODM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found documents: <comment>{$countModels}</comment></info>");
    }
}