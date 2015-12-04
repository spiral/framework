<?php
/**successfully*/
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Console\Configs\ConsoleConfig;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\DirectoriesInterface;
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
    protected $description = 'Configure file permissions, install modules and render view files';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['key', 'k', InputOption::VALUE_NONE, 'Generate new encryption key']
    ];

    /**
     * @param ConsoleConfig        $config
     * @param ConsoleDispatcher    $dispatcher
     * @param DirectoriesInterface $directories
     * @param FilesInterface       $files
     */
    public function perform(
        ConsoleConfig $config,
        ConsoleDispatcher $dispatcher,
        DirectoriesInterface $directories,
        FilesInterface $files
    ) {
        $this->ensurePermissions($directories, $files);

        $this->writeln("\n<info>Re-indexing available console commands.</info>");
        $dispatcher->command('console:reload', [], $this->output);

        $this->writeln("\n<info>Reloading bootload cache.</info>");
        $dispatcher->command('app:reload', [], $this->output);

        $this->writeln("\n<info>Re-loading translator locales cache...</info>");
        $dispatcher->command('i18n:reload', [], $this->output);

        $this->writeln("\n<info>Scanning translate function and [[values]] usage...</info>");
        $dispatcher->command('i18n:index', [], $this->output);

        $this->writeln("");

        //Additional commands
        foreach ($config->configureSequence() as $command => $options) {
            if (!empty($options['header'])) {
                $this->writeln($options['header']);
            }

            $dispatcher->command($command, $options['options'], $this->output);
            if (!empty($options['footer'])) {
                $this->writeln($options['footer']);
            }
        }

        if ($this->option('key')) {
            $this->writeln("");
            $dispatcher->command('app:key', [], $this->output);
        }

        $this->writeln("\n<info>All done!</info>");
    }

    /**
     * @param DirectoriesInterface $directories
     * @param FilesInterface       $files
     */
    protected function ensurePermissions(
        DirectoriesInterface $directories,
        FilesInterface $files
    ) {
        $this->writeln(
            "<info>Verifying runtime directory existence and file permissions...</info>"
        );

        $runtime = $directories->directory('runtime');

        if (!$files->exists(directory('runtime'))) {
            $files->ensureDirectory(directory('runtime'));
            $this->writeln("Runtime data directory was created.");

            return;
        }

        foreach ($files->getFiles(directory('runtime')) as $filename) {
            //Both file and it's directory must be writable
            $files->setPermissions($filename, FilesInterface::RUNTIME);
            $files->setPermissions(dirname($filename), FilesInterface::RUNTIME);

            if ($this->isVerbosity()) {
                $filename = $files->relativePath($filename, $runtime);
                $this->writeln("Permissions were updated for '<comment>{$filename}</comment>'.");
            }
        }

        $this->writeln("Runtime directory permissions updated.");
    }
}