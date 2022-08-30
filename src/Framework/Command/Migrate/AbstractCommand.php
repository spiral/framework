<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Migrate;

use Spiral\Console\Command;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
abstract class AbstractCommand extends Command
{
    /** @var Migrator */
    protected $migrator = null;

    /** @var MigrationConfig */
    protected $config = null;

    /**
     * @param Migrator        $migrator
     * @param MigrationConfig $config
     */
    public function __construct(Migrator $migrator, MigrationConfig $config)
    {
        $this->migrator = $migrator;
        $this->config = $config;

        parent::__construct();
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

        $this->writeln('<fg=red>Confirmation is required to run migrations!</fg=red>');

        if (!$this->askConfirmation()) {
            $this->writeln('<comment>Cancelling operation...</comment>');

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions(): array
    {
        return array_merge(
            static::OPTIONS,
            [
                ['force', 's', InputOption::VALUE_NONE, 'Skip safe environment check'],
            ]
        );
    }

    /**
     * @return bool
     */
    protected function askConfirmation(): bool
    {
        $question = new QuestionHelper();
        $confirmation = $question->ask(
            $this->input,
            $this->output,
            new ConfirmationQuestion('<question>Would you like to continue?</question> ')
        );

        return $confirmation;
    }
}
