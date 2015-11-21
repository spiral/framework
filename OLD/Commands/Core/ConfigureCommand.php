<?php
/**successfully*/
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Console\Configs\ConsoleConfig;
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
     * @param ConsoleConfig $config
     */
    public function perform(ConsoleConfig $config)
    {
        //TODO: COLLECT ERRORS


        $this->ensureRuntimeDirectory();

        //Updating modules
        $this->writeln("\n<info>Updating existed module resources...</info>");
        $this->writeln("<error>ERROR!</error>");
        //$this->console->command('modules:update', [], $this->output);

        //Updating commands cache
        $this->writeln("\n<info>Re-indexing available console commands.</info>");
        $this->console->command('console:reload', [], $this->output);

        //Indexing i18n usages
        $this->writeln("\n<info>Indexing translate function and classes usage...</info>");
        $this->writeln("<error>ERROR!</error>");
        //$this->console->command('i18n:index', [], $this->output);

        //Initiating view cache
        $this->writeln($this->isVerbosity() ? "\n<info>Generating view cache.</info>" : "");
        $this->writeln("<error>ERROR!</error>");
        //$this->console->command('view:compile', [], $this->output);

        if ($this->option('key')) {
            $this->writeln("");
            $this->writeln("<error>ERROR!</error>");
            //$this->console->command('app:key', [], $this->output);
        }

        //Additional commands
        foreach ($config->configureSequence() as $command => $options) {
            if (!empty($options['header'])) {
                $this->writeln($options['header']);
            }
            $this->console->command($command, $options['options'], $this->output);
            if (!empty($options['footer'])) {
                $this->writeln($options['footer']);
            }
        }

        $this->writeln("\n<info>All done!</info>");
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

            if ($this->isVerbosity()) {
                $filename = $this->files->relativePath($filename, directory('runtime'));
                $this->writeln("Permissions were updated for '<comment>{$filename}</comment>'.");
            }
        }

        $this->writeln("Runtime directory permissions updated.");
    }
}