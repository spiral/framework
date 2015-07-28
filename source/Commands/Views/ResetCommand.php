<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Views;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ResetCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'views:reset';

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
    protected $options = [
        ['emulate', 'e', InputOption::VALUE_NONE, 'If set, cache cleaning will be emulated.']
    ];

    /**
     * Clear view cache.
     */
    public function perform()
    {
        $this->isVerbose() && $this->writeln("<info>Clearing view cache:</info>");

        $cachedViews = $this->files->getFiles($this->views->getConfig()['caching']['directory']);
        foreach ($cachedViews as $filename)
        {
            !$this->option('emulate') && $this->files->delete($filename);
            $this->isVerbose() && $this->writeln($this->files->relativePath(
                $filename, directory('cache')
            ));
        }

        (empty($filename) && $this->isVerbose()) && $this->writeln("No cached views were found.");
        $this->writeln("<info>Cache is cleared.</info>");
    }
}