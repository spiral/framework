<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ORM;

use Spiral\Components\Console\Command;
use Spiral\Components\ORM\Exporters\UmlExporter;

use Symfony\Component\Console\Input\InputArgument;

class UmlCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'orm:uml';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Export ORM schema to UML.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    ];

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        $umlExporter = UmlExporter::make(['builder' => $this->orm->schemaBuilder()]);
        $umlExporter->render($this->argument('filename'));

        $this->writeln("<info>UML schema successfully created:</info> {$this->argument('filename')}");
    }
}