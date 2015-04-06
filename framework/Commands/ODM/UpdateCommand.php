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

class UpdateCommand extends Command
{
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
        $this->odm->updateSchema();
        $this->writeln("<info>ODM Schema and virtual documentation successfully updated.</info>");
    }
}