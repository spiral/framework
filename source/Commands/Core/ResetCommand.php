<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Core;

use Spiral\Console\Command;
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
    protected $options = [
        ['emulate', 'e', InputOption::VALUE_NONE, 'If set, cache cleaning will be emulated.']
    ];

    /**
     * Flushing application runtime cache.
     */
    public function perform()
    {
        $this->isVerbose() && $this->writeln("<info>Clearing application runtime cache:</info>");

        foreach ($this->files->getFiles(directory('cache')) as $filename)
        {
            !$this->option('emulate') && $this->files->delete($filename);
            $this->isVerbose() && $this->writeln($this->files->relativePath(
                $filename,
                directory('cache')
            ));
        }

        $this->writeln("<info>Runtime cache has been cleared.</info>");
    }
}