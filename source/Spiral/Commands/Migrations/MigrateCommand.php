<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations;

use Spiral\Commands\Migrations\Prototypes\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Execute one of multiple migrations.
 */
class MigrateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'migrate';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Perform one or all outstanding migrations.';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['quiet', 'q', InputOption::VALUE_NONE, 'Skip migrating if migrator not configured.'],
        ['one', 'o', InputOption::VALUE_NONE, 'Execute only one (first) migration.']
    ];

    /**
     * Execute one or multiple migrations.
     */
    public function perform()
    {
        if (!$this->migrator()->isConfigured()) {
            if ($this->option('quiet')) {
                $this->writeln("<comment>Migrator does not configured, skipping.</comment>");
            } else {
                $this->writeln("<fg=red>Migrator does not configured, unable to proceed.</fg=red>");
            }

            return;
        }

        if (!$this->verifyEnvironment()) {
            //Making sure we can safely migrate in this environment
            return;
        }

        $found = false;
        $count = $this->option('one') ? 1 : PHP_INT_MAX;
        while ($count > 0 && ($migration = $this->migrator()->run())) {
            $found = true;
            $count--;
            $this->writeln(interpolate(
                "<info>Migration <comment>{name}</comment> was successfully executed.</info>",
                ['name' => $migration->getStatus()->getName()]
            ));
        }

        if (!$found) {
            $this->writeln("<fg=yellow>No outstanding migrations were found.</fg=yellow>");
        }
    }
}