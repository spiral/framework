<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Modules;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Spiral\Console\Command;
use Spiral\Console\Helpers\ConsoleFormatter;
use Spiral\Debug\Logger;
use Spiral\Modules\DefinitionInterface;
use Spiral\Tokenizer\TokenizerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Install non installed module or modules. Provides ability to re-run installation at any moment.
 */
class InstallCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:install';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install available module(s) and mount it\'s resources.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['module', InputArgument::OPTIONAL, 'Module to be installed.']
    ];

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['all', 'a', InputOption::VALUE_NONE, 'Install all non installed modules.', null],
        ['force', 'f', InputOption::VALUE_NONE, 'Force modules installation (reinstall).', null],
    ];

    /**
     * Installer log level formats.
     *
     * @var array
     */
    protected $formats = [
        LogLevel::INFO    => 'fg=cyan',
        LogLevel::DEBUG   => '',
        LogLevel::WARNING => 'fg=yellow'
    ];

    /**
     * Perform command.
     *
     * @param TokenizerInterface $tokenizer
     */
    public function perform(TokenizerInterface $tokenizer)
    {
        if (empty($modules = $this->modules->findModules($tokenizer))) {
            $this->writeln(
                '<fg=red>'
                . 'No modules were found in any project file or library. Check Tokenizer config.'
                . '</fg=red>'
            );

            return;
        }

        if (empty($this->argument('module')) && !$this->option('all')) {
            $this->writeln(
                '<fg=red>'
                . 'You have to select module to install or '
                . 'force installation for all modules (<comment>--all</comment>).'
                . '</fg=red>'
            );

            return;
        }

        $countInstalled = 0;
        foreach ($modules as $definition) {
            if (!$this->option('all') && $definition->getName() != $this->argument('module')) {
                //Not target module
                continue;
            }

            if ($definition->isInstalled() && !$this->option('force')) {
                //Already installed
                continue;
            }

            $installer = $definition->getInstaller();

            //We might need to user console logger and LoggerAwareInterface in future
            if ($this->isVerbosing() && $installer instanceof LoggerAwareInterface) {
                $installer->setLogger($this->createLogger($definition));
            }

            $installer->install();

            //Registering in config
            $this->modules->registerModule($definition);
            $countInstalled++;

            $this->writeln(
                "Module '<comment>{$definition->getName()}</comment>' was successfully installed."
            );
        }

        if ($countInstalled == 0) {
            $this->writeln("<comment>No available modules were found.</comment>");
        }
    }

    /**
     * Installation logger (if needed).
     *
     * @param DefinitionInterface $definition
     * @return ConsoleFormatter
     */
    protected function createLogger(DefinitionInterface $definition)
    {
        return new ConsoleFormatter($this->output, $this->formats, $definition->getName());
    }
}