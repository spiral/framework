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
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'modules:update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update installed module resources.';

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
        $messenger = null;

        /**
         * @var FormatterHelper $formatter
         */
        $formatter = $this->getHelper('formatter');
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

            if (!$module->isInstalled())
            {
                continue;
            }

            $installer = $module->getInstaller();
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            {
                $installer->logger()->dispatcher()->addListener('message', $messenger);
            }

            $installer->update();
            $installer->logger()->dispatcher()->removeListener('message', $messenger);

            $this->modules->registerModule($module);
            $this->writeln(
                "Module '<comment>{$module->getName()}</comment>' successfully updated."
            );
        }
    }
}