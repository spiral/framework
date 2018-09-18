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
use Spiral\Translator\Catalogue\Manager;
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
     * @param Manager              $manager
     * @param InvocationsInterface $invocations
     * @param ClassesInterface     $classes
     */
    public function perform(
        TranslatorConfig $config,
        Manager $manager,
        InvocationsInterface $invocations,
        ClassesInterface $classes
    ) {
        $manager->reset();

        $catalogue = $manager->load(
            $this->argument('locale') ?? $config->defaultLocale()
        );

        $indexer = new Indexer($config, $catalogue);

        $this->writeln("Scanning <comment>l/p</comment> functions usage...");
        $indexer->indexInvocations($invocations);

        $this->writeln("Scanning <comment>TranslatorTrait</comment> usage...");
        $indexer->indexClasses($classes);

        $this->sprintf("Saving collected translations into `<comment>%s</comment>` locale.\n", $catalogue->getLocale());

        $manager->save($catalogue->getLocale());
    }
}