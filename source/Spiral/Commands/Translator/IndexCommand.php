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
use Spiral\Console\Command;
use Spiral\Tokenizer\ClassLocatorInterface;
use Spiral\Tokenizer\InvocationLocatorInterface;
use Spiral\Translator\Indexer;

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
    protected $name = 'i18n:index';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Index all declared translation strings and usages.';

    /**
     * @param Indexer                    $indexer
     * @param InvocationLocatorInterface $invocationLocator
     * @param ClassLocatorInterface      $classLocator
     */
    public function perform(
        Indexer $indexer,
        InvocationLocatorInterface $invocationLocator,
        ClassLocatorInterface $classLocator
    ) {
        if ($invocationLocator instanceof LoggerAwareInterface) {
            //Way too much verbosity
            $invocationLocator->setLogger(new NullLogger());
        }

        if ($classLocator instanceof LoggerAwareInterface) {
            //Way too much verbosity
            $classLocator->setLogger(new NullLogger());
        }

        $this->writeln("<info>Scanning translate function usages...</info>");
        $indexer->indexInvocations($invocationLocator);

        $this->writeln("<info>Scanning Translatable classes...</info>");
        $indexer->indexClasses($classLocator);
    }
}
