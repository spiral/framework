<?php

declare(strict_types=1);

namespace Spiral\Command\Translator;

use Spiral\Console\Command;
use Spiral\Core\Attribute\Singleton;
use Spiral\Translator\Catalogue\CatalogueManager;

#[Singleton]
final class ResetCommand extends Command
{
    protected const NAME        = 'i18n:reset';
    protected const DESCRIPTION = 'Reset translation cache';

    public function perform(CatalogueManager $manager): int
    {
        $manager->reset();
        $this->writeln('Translation cache has been reset.');

        return self::SUCCESS;
    }
}
