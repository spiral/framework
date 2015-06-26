<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Core;

use Spiral\Components\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'core:reset';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Reset application runtime cache and invalidate configs.';

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
     * Flushing application runtime cache.
     */
    public function perform()
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {
            $this->writeln("<info>Clearing application runtime cache:</info>");
        }

        foreach ($this->file->getFiles(directory('cache')) as $filename)
        {
            !$this->option('emulate') && $this->file->delete($filename);

            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            {
                $this->writeln($this->file->relativePath($filename, directory('cache')));
            }
        }

        $this->writeln("<info>Cache is cleared.</info>");
    }
}