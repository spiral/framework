<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\DBAL\Migrations;

use Spiral\Components\DBAL\Migrations\MigrationException;

class StatusCommand extends BaseCommand
{
    /**
     * Text to show if migration is not performed.
     */
    const NOT_PERFORMED = '<fg=red>not executed yet</fg=red>';

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Get list of all available migrations and their statuses.';

    /**
     * Getting migrations status.
     */
    public function perform()
    {
        if (!$this->getMigrator()->isConfigured())
        {
            throw new MigrationException("Migrator is not configured.");
        }

        $table = $this->table(array('Migration:', 'Filename:', 'Created at', 'Performed at'));
        foreach ($this->getMigrator()->getMigrations() as $migration)
        {
            $timePerformed = $migration['performed']
                ? '<info>' . date('Y-m-d H:i:s', $migration['performed']) . '</info>'
                : self::NOT_PERFORMED;

            $table->addRow(array(
                $migration['name'],
                $migration['filename'],
                date('Y-m-d H:i:s', $migration['timestamp']),
                $timePerformed
            ));
        }

        $table->render();
    }
}