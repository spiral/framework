<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Translator;

use Spiral\Console\Command;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Indexer;
use Symfony\Component\Console\Input\InputArgument;

class IndexCommand extends Command
{
    const NAME = 'i18n:index';
    const DESCRIPTION = 'Index all declared translation strings and usages';

    const ARGUMENTS = [
        ['locale', InputArgument::OPTIONAL, 'Locale to aggregate indexed translations into']
    ];

    /**
     * @param TranslatorConfig     $config
     * @param CataloguesInterface  $catalogues
     * @param InvocationsInterface $invocations
     * @param ClassesInterface     $classes
     */
    public function perform(
        TranslatorConfig $config,
        CataloguesInterface $catalogues,
        InvocationsInterface $invocations,
        ClassesInterface $classes
    ) {
        $catalogue = $catalogues->load(
            $this->argument('locale') ?? $config->defaultLocale()
        );

        $indexer = new Indexer($config, $catalogue);

        $this->writeln("Scanning <comment>L/P</comment> functions usage...");
        $indexer->indexInvocations($invocations);

        $this->writeln("Scanning <comment>TranslatorTrait</comment> users...");
        $indexer->indexClasses($classes);

        $this->sprintf(
            "<info>Saving collected translations into `<comment>%s</comment>` locale.</info>\n",
            $catalogue->getLocale()
        );
        $catalogues->save($catalogue->getLocale());
    }
}