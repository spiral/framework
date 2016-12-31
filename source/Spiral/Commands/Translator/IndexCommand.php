<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Translator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Spiral\Translator\Indexer;
use Spiral\Console\Command;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;

/**
 * Index available classes and function calls to fetch every used string translation. Can
 * understand l, p and translate (trait) function.
 *
 * @see Indexer
 */
class IndexCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'i18n:index';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Index all declared translation strings and usages';

    /**
     * @param Indexer             $indexer
     * @param InvocationsInterface $invocations
     * @param ClassesInterface    $classes
     */
    public function perform(
        Indexer $indexer,
        InvocationsInterface $invocations,
        ClassesInterface $classes
    ) {
        if ($invocations instanceof LoggerAwareInterface) {
            //Way too much verbosity
            $invocations->setLogger(new NullLogger());
        }

        if ($classes instanceof LoggerAwareInterface) {
            //Way too much verbosity
            $classes->setLogger(new NullLogger());
        }

        $this->writeln("<info>Scanning translate function usages...</info>");
        $indexer->indexInvocations($invocations);

        $this->writeln("<info>Scanning Translatable classes...</info>");
        $indexer->indexClasses($classes);
    }
}
