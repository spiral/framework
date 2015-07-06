<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\View;

use Spiral\Components\Console\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'view:cache';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Compile all available view files to create view cache.';

    /**
     * Compile available view files.
     */
    public function perform()
    {
        foreach ($this->view->getNamespaces() as $namespace => $directories)
        {
            //Reverted to treat priority
            $directories = array_reverse($directories);

            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            {
                $this->writeln("Rendering views in namespace '<comment>{$namespace}</comment>'.");
            }

            /**
             * @var FormatterHelper $formatter
             */
            $formatter = $this->getHelper('formatter');
            foreach ($directories as $directory)
            {
                $viewFiles = $this->file->getFiles($directory);
                foreach ($viewFiles as $filename)
                {
                    //View name (removing extension and ./)
                    $view = substr(
                        $this->file->relativePath($filename, $directory),
                        2,
                        -1 * (strlen($this->file->extension($filename)) + 1)
                    );

                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
                    {
                        $this->write($formatter->formatSection($namespace, $view . ", ", 'fg=cyan'));

                        $start = microtime(true);
                        $this->view->getFilename($namespace, $view, true, true);
                        $elapsed = number_format((microtime(true) - $start) * 1000);

                        $this->writeln("<comment>{$elapsed}</comment> ms");
                    }
                    elseif ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
                    {
                        $this->view->getFilename($namespace, $view, true, true);
                        $this->writeln($formatter->formatSection($namespace, $view, 'fg=cyan'));
                    }
                    else
                    {
                        if ($view != 'panel')
                        {
                            continue;
                        }

                        $this->view->getFilename($namespace, $view, true, true);
                    }
                }
            }
        }

        $this->writeln("<info>View cache successfully generated.</info>");
    }
}