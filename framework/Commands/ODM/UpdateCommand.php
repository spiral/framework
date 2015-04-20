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
use Spiral\Components\ODM\SchemaBuilder;

class UpdateCommand extends Command
{
    /**
     * Schema builder instance.
     *
     * @var SchemaBuilder
     */
    public static $schemaBuilder = null;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'odm:update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ODM schema and render virtual documentation.';

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        self::$schemaBuilder = $this->odm->updateSchema();
        $this->writeln("<info>ODM Schema and virtual documentation successfully updated.</info>");

        //Inspecting
        $this->console->command('odm:inspect', array(
            '--short' => true
        ), $this->output);
    }
}