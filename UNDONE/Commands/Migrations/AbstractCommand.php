<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\DBAL\Migrations;

use Spiral\Components\Console\Command;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Migrations\Migrator;
use Spiral\Core\Events\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    private $migrator = null;

    /**
     * Check if current environment is safe to run migration.
     *
     * @return bool
     */
    protected function checkEnvironment()
    {
        if ($this->option('safe') || $this->getMigrator()->isSafe()) {
            return true;
        }

        $this->writeln(
            "<fg=red>Current environment '{$this->core->getEnvironment()}' "
            . "is not safe to run migrations.</fg=red>"
        );

        if (!$this->ask->confirm("Do you wish to continue?")) {
            $this->writeln("<comment>Cancelling operation.</comment>");

            return false;
        }

        return true;
    }

    /**
     * Getting migrator instance.
     *
     * @return Migrator
     */
    protected function getMigrator()
    {
        if ($this->migrator) {
            return $this->migrator;
        }

        return $this->migrator = $this->dbal->getMigrator($this->option('database'));
    }

    /**
     * Executes the current command.
     * This method is not abstract because you can use this class as a concrete class. In this case,
     * instead of defining the execute() method, you set the code to execute by passing a Closure to
     * the setCode() method.
     *
     * Method will pass call to perform() method with DI.
     *
     * @see setCode()
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Database driver
        $driver = $this->getMigrator()->getDatabase()->getDriver();

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $driver->dispatcher()->addListener('statement', [$this, 'displayQuery']);
        }

        $this->callFunction('perform', compact('input', 'output'));

        $driver->dispatcher()->removeListener('statement', [$this, 'displayQuery']);
    }

    /**
     * Command options. By default "options" property will be used.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge($this->options, [
            ['database', 'd', InputOption::VALUE_OPTIONAL, 'Database instance to use.', 'default'],
            ['safe', 's', InputOption::VALUE_NONE, 'Skip safe environment check.']
        ]);
    }
}