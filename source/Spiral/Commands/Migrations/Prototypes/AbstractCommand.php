<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Migrations\Prototypes;

use Interop\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Migrations\Configs\MigrationsConfig;
use Spiral\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
    /**
     * @var Migrator
     */
    protected $migrator = null;

    /**
     * @var MigrationsConfig
     */
    protected $config = null;

    /**
     * @param Migrator           $migrator
     * @param MigrationsConfig   $config
     * @param ContainerInterface $container
     */
    public function __construct(
        Migrator $migrator,
        MigrationsConfig $config,
        ContainerInterface $container
    ) {
        parent::__construct($container);

        $this->migrator = $migrator;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    protected function verifyConfigured(): bool
    {
        if (!$this->migrator->isConfigured()) {
            $this->writeln(
                "<fg=red>Migrations are not configured yet, run '<info>migrate:init</info>' first.</fg=red>"
            );

            return false;
        }

        return true;
    }

    /**
     * Check if current environment is safe to run migration.
     *
     * @return bool
     */
    protected function verifyEnvironment(): bool
    {
        if ($this->option('force') || $this->config->isSafe()) {
            //Safe to run
            return true;
        }

        $this->writeln("<fg=red>Confirmation is required to run migrations!</fg=red>");

        if (!$this->ask()->confirm("Do you wish to continue?")) {
            $this->writeln("<comment>Cancelling operation...</comment>");

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions(): array
    {
        return array_merge(static::OPTIONS, [
            ['force', 's', InputOption::VALUE_NONE, 'Skip safe environment check']
        ]);
    }
}