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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'configure';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Configure file permissions, install modules and render view files.';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['key', 'k', InputOption::VALUE_NONE, 'Generate new encryption key.']
    );

    /**
     * Configuring spiral application.
     */
    public function perform()
    {
        $this->writeln("<info>Verifying runtime directory existence and permissions.</info>");

        if (!$this->file->exists(directory('runtime')))
        {
            $this->file->ensureDirectory(directory('runtime'));
            $this->writeln("Runtime data directory created.");
        }
        else
        {
            foreach ($this->file->getFiles(directory('runtime')) as $filename)
            {
                $this->file->setPermissions($filename, FileManager::RUNTIME, true);
                $this->file->setPermissions(dirname($filename), FileManager::RUNTIME, true);

                $filename = $this->file->relativePath($filename, directory('runtime'));

                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
                {
                    $this->writeln("Permissions updated for '<comment>{$filename}</comment>'.");
                }
            }

            $this->writeln("Runtime directory permissions updated.");
        }

        $this->writeln("\n<info>Updating configuration cache.</info>");
        $this->console->command('core:touch', array(), $this->output);

        //Installing modules
        $this->writeln("\n<info>Installing all available modules.</info>");
        $this->console->command('modules:install', array(), $this->output);

        //Updating commands cache
        $this->writeln("\n<info>Re-indexing available console commands.</info>");
        $this->console->command('console:refresh', array(), $this->output);

        //Indexing i18n usages
        $this->writeln("\n<info>Creating initial i18n bundles cache.</info>");
        $this->console->command('i18n:index', array(), $this->output);

        //Initiating view cache
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
        {
            $this->writeln("\n<info>Generating view cache.</info>");
        }
        else
        {
            $this->writeln("");
        }

        $this->console->command('view:cache', array(), $this->output);

        if ($this->option('key'))
        {
            $this->writeln("");
            $this->console->command('core:key', array(), $this->output);
        }

        $this->writeln("\n<info>Application configured.</info>");
    }
}