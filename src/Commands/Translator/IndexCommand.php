<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Commands\Translator;

use Spiral\Console\Command;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Indexer;

class IndexCommand extends Command
{
    const NAME        = 'i18n:index';
    const DESCRIPTION = 'Index all declared translation strings and usages';

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
        $catalogue = $catalogues->load('en');

        $indexer = new Indexer($config, $catalogue);

        $this->writeln("<info>Scanning translate function usages...</info>");
        $indexer->indexInvocations($invocations);

        $this->writeln("<info>Scanning Translatable classes...</info>");
        $indexer->indexClasses($classes);

        $catalogues->save('en');
    }
}