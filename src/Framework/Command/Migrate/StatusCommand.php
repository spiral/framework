<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Migrate;

use Spiral\Files\FilesInterface;
use Spiral\Migrations\State;

/**
 * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
 * Show all available migrations and their statuses
 */
final class StatusCommand extends AbstractCommand
{
    protected const NAME        = 'migrate:status';
    protected const DESCRIPTION = 'Get list of all available migrations and their statuses';
    protected const PENDING     = '<fg=red>not executed yet</fg=red>';

    /**
     * @param FilesInterface $files
     */
    public function perform(FilesInterface $files): void
    {
        $this->migrator->configure();

        if (empty($this->migrator->getMigrations())) {
            $this->writeln('<comment>No migrations were found.</comment>');

            return;
        }

        $table = $this->table(['Migration', 'Created at', 'Executed at']);
        foreach ($this->migrator->getMigrations() as $migration) {
            $state = $migration->getState();

            $table->addRow(
                [
                    $state->getName(),
                    $state->getTimeCreated()->format('Y-m-d H:i:s'),
                    $state->getStatus() == State::STATUS_PENDING
                        ? self::PENDING
                        : '<info>' . $state->getTimeExecuted()->format('Y-m-d H:i:s') . '</info>',
                ]
            );
        }

        $table->render();
    }
}
