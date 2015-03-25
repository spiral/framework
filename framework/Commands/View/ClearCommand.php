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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'view:clear';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Clear view cache for all environments.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['emulate', 'e', InputOption::VALUE_NONE, 'If set, cache cleaning will be emulated.']
    );

    /**
     * Clear view cache.
     */
    public function perform()
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {
            $this->writeln("<info>Clearing view cache:</info>");
        }

        $cacheFiles = $this->file->getFiles($this->view->getConfig()['caching']['directory']);
        foreach ($cacheFiles as $filename)
        {
            !$this->option('emulate') && $this->file->remove($filename);

            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            {
                $this->writeln($this->file->relativePath($filename, directory('cache')));
            }
        }

        if (empty($filename) && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {
            $this->writeln("No cached views were found.");
        }

        $this->writeln("<info>Cache is cleared.</info>");
    }
}