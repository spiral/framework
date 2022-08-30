<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Migrate;

/**
 * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class InitCommand extends AbstractCommand
{
    protected const NAME        = 'migrate:init';
    protected const DESCRIPTION = 'Init migrations component (create migrations table)';

    /**
     * Perform command.
     */
    public function perform(): void
    {
        $this->migrator->configure();
        $this->writeln('<info>Migrations table were successfully created</info>');
    }
}
