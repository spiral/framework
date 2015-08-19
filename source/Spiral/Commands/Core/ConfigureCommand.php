<?php
/**successfully*/
namespace Spiral\Commands\Core;

use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs set of commands after project installation or update. Update permissions in runtime
 * directory, installs and updates modules, indexes available console commands, compiles view cache.
 *
 * Can additionally set encryption key if requested.
 */
class ConfigureCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'configure';
    /**
     * {@inheritdoc}
     */
    protected $description = 'Configure file permissions, install modules and render view files.';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['key', 'k', InputOption::VALUE_NONE, 'Generate new encryption key.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->ensureRuntimeDirectory();

        $this->writeln("\n<info>Updating configuration cache...</info>");
        $this->console->command('core:touch', [], $this->output);

        //Installing modules
        $this->writeln("\n<info>Installing all available modules...</info>");
        $this->console->command('modules:install', ['--all' => true], $this->output);

        //Updating modules
        $this->writeln("\n<info>Updating existed module resources...</info>");
        $this->console->command('modules:update', [], $this->output);

        //Updating commands cache
        $this->writeln("\n<info>Re-indexing available console commands.</info>");
        $this->console->command('console:refresh', [], $this->output);

        //Indexing i18n usages
        $this->writeln("\n<info>Indexing translate function and classes usage...</info>");
        $this->console->command('i18n:index', [], $this->output);

        //Initiating view cache
        $this->writeln($this->isVerbosing() ? "\n<info>Generating view cache.</info>" : "");
        $this->console->command('view:compile', [], $this->output);

        if ($this->option('key')) {
            $this->writeln("");
            $this->console->command('core:key', [], $this->output);
        }

        //Additional commands
        foreach ($this->console->config()['configureSequence'] as $command => $options) {
            if (!empty($options['header'])) {
                $this->writeln($options['header']);
            }
            $this->console->command($command, $options['options'], $this->output);
            if (!empty($options['footer'])) {
                $this->writeln($options['footer']);
            }
        }

        $this->writeln("\n<info>Application were successfully configured.</info>");
    }

    /**
     * Ensure existence and permissions of runtime directory.
     */
    protected function ensureRuntimeDirectory()
    {
        $this->writeln("<info>Verifying runtime directory existence and file permissions...</info>");

        if (!$this->files->exists(directory('runtime'))) {
            $this->files->ensureLocation(directory('runtime'));
            $this->writeln("Runtime data directory was created.");

            return;
        }

        foreach ($this->files->getFiles(directory('runtime')) as $filename) {
            //Both file and it's directory must be writable
            $this->files->setPermissions($filename, FilesInterface::RUNTIME);
            $this->files->setPermissions(dirname($filename), FilesInterface::RUNTIME);

            if ($this->isVerbosing()) {
                $filename = $this->files->relativePath($filename, directory('runtime'));
                $this->writeln("Permissions were updated for '<comment>{$filename}</comment>'.");
            }
        }

        $this->writeln("Runtime directory permissions updated.");
    }
}