<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;

/**
 * Initiate migrations.
 */
class InitCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'migrate:init';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Configure migrations (create migrations table).';

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->migrator()->configure();
        $this->writeln("<info>Migrations table successfully created.</info>");
    }
}