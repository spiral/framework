<?php

declare(strict_types=1);

namespace Spiral\Console\Confirmation;

use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\Attribute\Scope;
use Spiral\Framework\Spiral;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The component makes it easy to ask the user for confirmation before running
 * a command if the application is running in production mode.
 * This can help prevent accidental or unintended changes to the production environment.
 */
#[Scope(Spiral::ConsoleCommand)]
final class ApplicationInProduction
{
    use HelpersTrait;

    public function __construct(
        private readonly AppEnvironment $appEnv,
        InputInterface $input,
        OutputInterface $output,
    ) {
        $this->input = $input;
        $this->output = $output instanceof SymfonyStyle ? $output : new SymfonyStyle($input, $output);
    }

    public function confirmToProceed(string $message = 'Application in production.'): bool
    {
        if (!$this->appEnv->isProduction()) {
            return true;
        }

        if ($this->hasOption('force') && $this->option('force')) {
            return true;
        }

        $this->alert($message);

        $confirmed = $this->confirm('Do you really wish to run command?');

        if (!$confirmed) {
            $this->comment('Command Canceled!');

            return false;
        }

        return true;
    }
}
