<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;

class InitCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'migrate:init';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Init migrations component (create migrations table)';

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->migrator->configure();
        $this->writeln("<info>Migrations table were successfully created</info>");
    }
}