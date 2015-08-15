<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations\Prototypes;

use Spiral\Console\Command;
use Spiral\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides environment check and migrator creation for every migration command.
 */
class AbstractCommand extends Command
{
    /**
     * @var Migrator
     */
    private $migrator = null;

    /**
     * @return Migrator
     */
    protected function migrator()
    {
        if ($this->migrator) {
            return $this->migrator;
        }

        return $this->migrator = $this->container->get(Migrator::class);
    }

    /**
     * Check if current environment is safe to run migration.
     *
     * @return bool
     */
    protected function verifyEnvironment()
    {
        if ($this->option('safe')) {
            //Safe to run
            return true;
        }

        if (in_array($this->core->environment(), $this->migrator()->config()['environments'])) {
            //Safe to run
            return true;
        }

        $this->writeln(interpolate(
            "<fg=red>Application environment '{environment}' does not allowed to run migrations.</fg=red>",
            ['environment' => $this->core->environment()]
        ));

        if (!$this->ask()->confirm("Do you wish to continue?")) {
            $this->writeln("<comment>Cancelling operation.</comment>");

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return array_merge($this->options, [
            ['safe', 's', InputOption::VALUE_NONE, 'Skip safe environment check.']
        ]);
    }
}