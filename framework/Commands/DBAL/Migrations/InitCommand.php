<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\DBAL\Migrations;

class InitCommand extends BaseCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'migrate:configure';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Configure migrations (create migrations table).';

    /**
     * Initiating migration table.
     */
    public function perform()
    {
        $this->getMigrator()->configure();
        $this->writeln("<info>Migrations table successfully created.</info>");
    }
}