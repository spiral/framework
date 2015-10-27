<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Core;

use Spiral\Console\Command;
use Spiral\Core\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Changes application environment by writing custom value into application/data/environment.php
 * Works only with default spiral core. Run command without any argument to check current
 * environment.
 */
class EnvironmentCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'environment';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Show/change application environment (data/environment.php).';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['environment', InputArgument::OPTIONAL, 'Environment name.']
    ];

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['configure', 'c', InputOption::VALUE_NONE, 'Reconfigure application.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        if (empty($this->argument('environment'))) {
            $this->writeln(
                "Current application environment: <comment>{$this->core->environment()}</comment>."
            );

            return;
        }

        //That's easy
        $this->core->saveData('environment', $this->argument('environment'), directory('runtime'));
        $this->writeln("Environment set to <comment>{$this->argument('environment')}</comment>.");

        if ($this->isVerbosing()) {
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
            $this->console->command('configure', [], $this->output);
        } else {
            $this->console->command('app:touch', [], $this->output);
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
        $configDirectory = $this->files->normalizePath(directory('config'), true);
        $environmentDirectory = $configDirectory . "{$environment}/";

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