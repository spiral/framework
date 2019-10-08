<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command\Migrate;

final class InitCommand extends AbstractCommand
{
    public const NAME        = 'migrate:init';
    public const DESCRIPTION = 'Init migrations component (create migrations table)';

    /**
     * Perform command.
     */
    public function perform(): void
    {
        $this->migrator->configure();
        $this->writeln('<info>Migrations table were successfully created</info>');
    }
}
