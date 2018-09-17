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
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Indexer;
use Spiral\Translator\Translator;

class IndexCommand extends Command
{
    const NAME        = 'i18n:index';
    const DESCRIPTION = 'Index all declared translation strings and usages';

    /**
     * @param InvocationsInterface $invocations
     * @param ClassesInterface     $classes
     */
    public function perform(
        InvocationsInterface $invocations,
        ClassesInterface $classes,
        TranslatorConfig $config,
        Translator $translator
    ) {
        $c = $translator->getCatalogues()->load('en');
        $indexer = new Indexer($config, $c);

//        if ($invocations instanceof LoggerAwareInterface) {
//            //Way too much verbosity
//            $invocations->setLogger(new NullLogger());
//        }
//        if ($classes instanceof LoggerAwareInterface) {
//            //Way too much verbosity
//            $classes->setLogger(new NullLogger());
//        }

        $this->writeln("<info>Scanning translate function usages...</info>");
        $indexer->indexInvocations($invocations);

        $this->writeln("<info>Scanning Translatable classes...</info>");
        $indexer->indexClasses($classes);

        //Make sure that all located messages are properly registered
        //$translator->syncLocales();
    }
}