<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Console\Command;
use Spiral\Documenters\ODM\UmlExporter;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Exports ODM schema into UML format.
 */
class UmlCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'odm:uml';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Export ODM schema to UML.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * @var UmlExporter $uml
         */
        $uml = $this->container->construct(UmlExporter::class, [
            'builder' => $this->odm->schemaBuilder()
        ]);

        $uml->export($this->argument('filename'));
        $this->writeln("<info>UML schema successfully exported:</info> {$this->argument('filename')}");
    }
}