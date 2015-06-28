<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Modules;

use Spiral\Components\Console\Command;
use Spiral\Core\Events\ObjectEvent;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'modules:install';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Install all available modules and mount their resources.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['module', InputArgument::OPTIONAL, 'Module to be installed.']
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['all', 'a', InputOption::VALUE_NONE, 'Install all non installed modules.', null],
        ['force', 'f', InputOption::VALUE_NONE, 'Force modules installation (reinstall).', null],
    );

    /**
     * Mount available modules and update their resources.
     */
    public function perform()
    {
        if (!$modules = $this->modules->findModules())
        {
            $this->writeln(
                '<fg=red>No modules were found in any project file or library. '
                . 'Check Tokenizer config.</fg=red>'
            );

            return;
        }

        if (!$this->argument('module') && !$this->option('all'))
        {
            $this->writeln(
                '<fg=red>You have to select module to install'
                . ' or force installation for all modules (<comment>--all</comment>).</fg=red>'
            );

            return;
        }

        $messenger = null;

        /**
         * @var FormatterHelper $formatter
         */
        $formatter = $this->getHelper('formatter');

        $countInstalled = 0;
        foreach ($modules as $module)
        {
            $messenger = function (ObjectEvent $event) use ($module, $formatter)
            {
                $this->writeln(
                    $formatter->formatSection(
                        $module->getName(),
                        $event->context['message'],
                        'fg=cyan'
                    )
                );
            };

            if (!$this->option('all') && $module->getName() != $this->argument('module'))
            {
                continue;
            }

            if ($module->isInstalled() && !$this->option('force'))
            {
                continue;
            }

            $installer = $module->getInstaller();
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            {
                $installer->logger()->dispatcher()->addListener('message', $messenger);
            }

            $installer->install();
            $installer->logger()->dispatcher()->removeListener('message', $messenger);

            $this->modules->registerModule($module);

            $countInstalled++;

            $this->writeln(
                "Module '<comment>{$module->getName()}</comment>' successfully installed."
            );
        }

        if ($countInstalled == 0)
        {
            $this->writeln("<info>No non installed modules found.</info>");
        }
    }
}