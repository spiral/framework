<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Migrations;

class InitCommand extends AbstractCommand
{
    const NAME        = 'migrate:init';
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