<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Translator;

use Spiral\Console\Command;
use Spiral\Events\Entities\ObjectEvent;
use Spiral\Translator\Indexer;
use Symfony\Component\Console\Input\InputOption;

/**
 * Index available classes and function calls to fetch every used string translation. Can understand
 * l, p, translate (trait) function and I18n proxy methods. Only statically calls will be indexes.
 *
 * In addition index will find every string specified in default value of model or class which
 * uses TranslatorTrait. String has to be embraced with [[ ]] in order to be indexed, you can disable
 * property indexation using @do-not-index doc comment. Translator can merge strings with parent data,
 * set class constant INHERIT_TRANSLATIONS to true.
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
     * {@inheritdoc}
     */
    protected $options = [
        [
            'directory',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Directory to scan for translate function usages.'
        ]
    ];

    /**
     * Perform command.
     *
     * @param Indexer $indexer
     */
    public function perform(Indexer $indexer)
    {
        $this->writeln("Scanning translate function usages...");
        $this->isVerbose() && $indexer->events()->listen('string', $this->stringListener());

        if ($this->option('directory')) {
            $indexer->indexDirectory($this->option('directory'));
        } else {
            foreach ($this->tokenizer->config()['directories'] as $directory) {
                $indexer->indexDirectory($directory, $this->tokenizer->config()['exclude']);
            }
        }

        $this->writeln("Scanning Translatable classes...");
        $indexer->indexClasses();

        $this->writeln(
            "<info>Strings found: <comment>{$indexer->countStrings()}</comment> "
            . "in <comment>{$indexer->countBundles()}</comment> bundle(s).</info>"
        );
    }

    /**
     * Realtime string highlighter.
     *
     * @return \Closure
     */
    private function stringListener()
    {
        return function (ObjectEvent $event) {
            $this->writeln("<fg=magenta>{$event->context()['string']}</fg=magenta>");

            if ($event->context()['class']) {
                $this->writeln("In class <comment>{$event->context()['class']}</comment>");

                return;
            }

            $filename = $this->files->relativePath($event->context()['filename'],
                directory('root'));
            $this->writeln(
                "In <comment>{$filename}</comment> at line <comment>{$event->context()['line']}</comment>"
            );
        };
    }
}
