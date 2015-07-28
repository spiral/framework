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
use Spiral\Events\ObjectEvent;
use Spiral\Translator\Indexer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'i18n:index';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Index all declared translation strings and usages.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = [
        ['directory', 'd', InputOption::VALUE_OPTIONAL, 'Directory to scan for translate function usages.']
    ];

    /**
     * Running indexation.
     *
     * @param Indexer $indexer
     */
    public function perform(Indexer $indexer)
    {
        $this->isVerbose() && $indexer->events()->addListener('string', $this->getListener());

        $this->writeln("Scanning translate function usages...");

        if ($this->option('directory'))
        {
            $indexer->indexDirectory($this->option('directory'));
        }
        else
        {
            foreach ($this->tokenizer->getConfig()['directories'] as $directory)
            {
                $indexer->indexDirectory($directory, $this->tokenizer->getConfig()['exclude']);
            }
        }

        $this->writeln("Scanning Translatable classes...");
        $indexer->indexClasses();

        $totalStrings = 0;
        $bundles = count($indexer->getBundles());
        foreach ($indexer->getBundles() as $bundle)
        {
            $totalStrings += count($bundle);
        }

        $this->writeln(
            "<info>Strings found: <comment>{$totalStrings}</comment> "
            . "in <comment>{$bundles}</comment> bundle(s).</info>"
        );
    }

    /**
     * Verbosity listener.
     *
     * @return callable
     */
    protected function getListener()
    {
        return function (ObjectEvent $event)
        {
            $this->writeln("<fg=magenta>{$event->context()['string']}</fg=magenta>");

            if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE)
            {
                //This extra information is pretty extra one...
                return;
            }

            if ($event->context()['class'])
            {
                $this->writeln("In class <comment>{$event->context()['class']}</comment>");
            }
            else
            {
                $filename = $this->files->relativePath($event->context()['filename'], directory('root'));
                $this->writeln(
                    "In <comment>{$filename}</comment> at line <comment>{$event->context()['line']}</comment>"
                );
            }
        };
    }
}
