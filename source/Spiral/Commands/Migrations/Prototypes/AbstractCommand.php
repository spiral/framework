<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations\Prototypes;

use Psr\Log\LogLevel;
use Spiral\Console\Command;
use Spiral\Console\Helpers\ConsoleFormatter;
use Spiral\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Table schema log formats in verbosity mode.
     *
     * @var array
     */
    protected $formats = [
        LogLevel::INFO    => 'fg=cyan',
        LogLevel::DEBUG   => '',
        LogLevel::WARNING => 'fg=yellow'
    ];

    /**
     * @return Migrator
     */
    protected function migrator()
    {
        if ($this->migrator) {
            return $this->migrator;
        }

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            //Let's show SQL queries
            foreach ($this->dbal->getDrivers() as $driver) {
                $driver->setLogger(
                    new ConsoleFormatter($this->output, $this->formats, $driver->getName())
                );
            }
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
            "<fg=red>Environment '{environment}' requires confirmation to run migrations.</fg=red>",
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