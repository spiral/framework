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

class UpdateCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'orm:update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ORM schema and render virtual documentation.';

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        $this->orm->updateSchema();
        $this->writeln("<info>ORM Schema and virtual documentation successfully updated.</info>");
    }
}