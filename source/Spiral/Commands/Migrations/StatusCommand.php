<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;
use Spiral\Files\FilesInterface;
use Spiral\Migrations\Migration\State;

/**
 * Show all available migrations and their statuses
 */
class StatusCommand extends AbstractCommand
{
    /**
     * Text to show if migration is not performed.
     */
    const PENDING = '<fg=red>not executed yet</fg=red>';

    /**
     * {@inheritdoc}
     */
    const NAME = 'migrate:status';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Get list of all available migrations and their statuses';

    /**
     * @param FilesInterface $files
     */
    public function perform(FilesInterface $files)
    {
        if (!$this->verifyConfigured()) {
            return;
        }

        if (empty($this->migrator->getMigrations())) {
            $this->writeln("<comment>No migrations were found.</comment>");

            return;
        }

        $table = $this->table(['Migration', 'Filename', 'Created at', 'Executed at']);
        foreach ($this->migrator->getMigrations() as $migration) {
            $filename = (new \ReflectionClass($migration))->getFileName();

            $state = $migration->getState();

            $table->addRow([
                $state->getName(),
                '<comment>'
                . $files->relativePath($filename, $this->config->getDirectory())
                . '</comment>',
                $state->getTimeCreated()->format('Y-m-d H:i:s'),
                $state->getStatus() == State::STATUS_PENDING
                    ? self::PENDING
                    : '<info>' . $state->getTimeExecuted()->format('Y-m-d H:i:s') . '</info>'
            ]);
        }

        $table->render();
    }
}