<?php

declare(strict_types=1);

namespace Spiral\App\Command;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\Console\Confirmation\ApplicationInProduction;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app-in-production')]
final class ApplicationInProductionCommand extends Command
{
    public function __invoke(ApplicationInProduction $confirmation, OutputInterface $output): int
    {
        if ($confirmation->confirmToProceed('Application in production.')) {
            $this->writeln('Application is in production.');
            return self::SUCCESS;
        }

        $this->writeln('Application is in testing.');

        return self::SUCCESS;
    }
}
