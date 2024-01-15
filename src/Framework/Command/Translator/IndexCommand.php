<?php

declare(strict_types=1);

namespace Spiral\Command\Translator;

use Spiral\Console\Command;
use Spiral\Core\Attribute\Singleton;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Indexer;
use Symfony\Component\Console\Input\InputArgument;

#[Singleton]
final class IndexCommand extends Command
{
    protected const NAME        = 'i18n:index';
    protected const DESCRIPTION = 'Index all declared translation strings and usages';
    protected const ARGUMENTS   = [
        ['locale', InputArgument::OPTIONAL, 'Locale to aggregate indexed translations into'],
    ];

    public function perform(
        TranslatorConfig $config,
        CatalogueManager $manager,
        InvocationsInterface $invocations,
        ScopedClassesInterface $classes
    ): int {
        $catalogue = $manager->load($this->argument('locale') ?? $config->getDefaultLocale());

        $indexer = new Indexer($config, $catalogue);

        $this->writeln('Scanning <comment>l/p</comment> functions usage...');
        $indexer->indexInvocations($invocations);

        $this->writeln('Scanning <comment>TranslatorTrait</comment> usage...');
        $indexer->indexClasses($classes);

        $this->sprintf(
            "Saving collected translations into `<comment>%s</comment>` locale.\n",
            $catalogue->getLocale()
        );

        $manager->save($catalogue->getLocale());

        return self::SUCCESS;
    }
}
