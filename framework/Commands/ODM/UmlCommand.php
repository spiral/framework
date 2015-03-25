<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Components\Console\Command;
use Spiral\Components\ODM\Exporters\UmlExporter;
use Spiral\Components\ODM\ODM;
use Symfony\Component\Console\Input\InputArgument;

class UmlCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'odm:uml';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Export ODM schema to UML.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    );

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        $umlExporter = UmlExporter::make(array('schema' => $this->odm->schemaReader()));
        $umlExporter->render($this->argument('filename'));

        $this->writeln("<info>UML schema successfully created:</info> {$this->argument('filename')}");
    }
}