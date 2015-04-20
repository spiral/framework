<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\I18n;

use Spiral\Components\Console\Command;
use Spiral\Components\I18n\Indexer;
use Spiral\Core\Events\ObjectEvent;
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
    protected $description = 'Index all declared i18n strings and usages.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['directory', 'd', InputOption::VALUE_OPTIONAL, 'Directory to scan for i18n function usages.']
    );

    /**
     * Running indexation.
     */
    public function perform()
    {
        $indexer = Indexer::make();

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {
            $indexer::dispatcher()->addListener('string', function (ObjectEvent $event)
            {
                $this->writeln("<fg=magenta>{$event->context['string']}</fg=magenta>");

                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE)
                {
                    if ($event->context['class'])
                    {
                        $this->writeln(
                            "In class <comment>{$event->context['class']}</comment>"
                        );
                    }
                    else
                    {
                        $filename = $this->file->relativePath($event->context['filename']);
                        $this->writeln(
                            "In <comment>{$filename}</comment> "
                            . "at line <comment>{$event->context['line']}</comment>"
                        );
                    }
                }
            });
        }

        $this->writeln("Scanning i18n function usages...");
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

        $this->writeln("Scanning Localizable classes...");
        $indexer->indexClasses();

        $bundles = count($indexer->foundStrings());
        $totalUsages = 0;
        foreach ($indexer->foundStrings() as $bundle)
        {
            $totalUsages += count($bundle);
        }

        $this->writeln(
            "<info>Strings found: <comment>{$totalUsages}</comment> "
            . "in <comment>{$bundles}</comment> bundle(s).</info>"
        );
    }
}