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
use Spiral\Components\Files\FileManager;
use Spiral\Core\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnvironmentCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'core:environment';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Change application environment.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments() method.
     *
     * @var array
     */
    protected $arguments = array(
        ['environment', InputArgument::REQUIRED, 'Environment name.']
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions() method.
     *
     * @var array
     */
    protected $options = array(
        ['configure', 'c', InputOption::VALUE_NONE, 'Reconfigure application after update.']
    );

    /**
     * Updating application environment.
     */
    public function perform()
    {
        $this->core->saveData('environment', $this->argument('environment'), directory('runtime'));
        $this->writeln("Environment set to '<comment>{$this->argument('environment')}</comment>'.");

        //We have to touch every config to ensure that cache is OK

        $configDirectory = $this->file->normalizePath(directory('config'));

        $alteredConfigs = array();
        $configs = $this->file->getFiles($configDirectory, substr(Core::CONFIGS, 1));
        foreach ($configs as $filename)
        {
            $environmentConfig = $configDirectory
                . "/{$this->argument('environment')}/" . basename($filename);

            if (dirname($filename) == $configDirectory && $this->file->exists($environmentConfig))
            {
                $alteredConfigs[] = $this->file->relativePath($filename, $configDirectory);
            }

            //Touching
            $this->file->touch($filename);
        }

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {

            if (!empty($alteredConfigs))
            {
                $this->writeln(
                    "<info>Following configuration files will be altered by this environment:</info>"
                );

                foreach ($alteredConfigs as $filename)
                {
                    $this->writeln($filename);
                }
            }
        }

        if ($this->option('configure'))
        {
            $this->console->command('core:configure', array(), $this->output);
        }
    }
}