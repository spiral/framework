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
use Spiral\Core\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Changes application environment by writing custom value into application/data/environment.php
 * Works only with default spiral core.
 */
class EnvironmentCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'core:environment';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Change application environment (data/environment.php).';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['environment', InputArgument::REQUIRED, 'Environment name.']
    ];

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = [
        ['configure', 'c', InputOption::VALUE_NONE, 'Reconfigure application.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        //That's easy
        $this->core->saveData('environment', $this->argument('environment'), directory('runtime'));
        $this->writeln("Environment set to '<comment>{$this->argument('environment')}</comment>'.");

        if ($this->isVerbose()) {
            $alteredConfigs = $this->getAlteredConfigs($this->argument('environment'));
            if (!empty($alteredConfigs)) {
                $this->writeln(
                    "<info>Following configuration files were altered by this environment:</info>"
                );

                foreach ($alteredConfigs as $filename) {
                    $this->writeln("<comment>{$filename}</comment>");
                }
            }
        }

        if ($this->option('configure')) {
            $this->console->command('core:configure', [], $this->output);
        } else {
            $this->console->command('core:touch', [], $this->output);
        }
    }

    /**
     * List of configs affected by environment change.
     *
     * @param string $environment
     * @return array
     */
    protected function getAlteredConfigs($environment)
    {
        //We have to touch every config to ensure that cache is OK
        $configDirectory = $this->files->normalizePath(directory('config'));
        $environmentDirectory = $configDirectory . "/{$environment}/";

        $altered = [];
        foreach ($this->files->getFiles($configDirectory, Core::EXTENSION) as $filename) {
            $environmentConfig = $environmentDirectory . basename($filename);

            if (dirname($filename) == $configDirectory && $this->files->exists($environmentConfig)) {
                $altered[] = $this->files->relativePath($filename, $configDirectory);
            }
        }

        return $altered;
    }
}