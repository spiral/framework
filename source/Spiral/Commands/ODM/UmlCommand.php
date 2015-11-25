<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\ODM;

use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\ODM\ODM;
use Spiral\ODM\UmlExporter;
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
     * @param FilesInterface $files
     * @param ODM            $odm
     */
    public function perform(FilesInterface $files, ODM $odm)
    {
        $umlExporter = new UmlExporter($odm->schemaBuilder());

        $files->write($this->argument('filename'), $umlExporter->generate());
        $this->writeln(
            "<info>UML schema has been  successfully exported:</info> {$this->argument('filename')}"
        );
    }
}