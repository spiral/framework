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
use Spiral\Tokenizer\TokenizerInterface;

/**
 * Update public resources of already installed modules
 */
class UpdateCommand extends InstallCommand
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
    protected $description = 'Update public resources of already installed modules.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [];

    /**
     * {@inheritdoc}
     */
    protected $options = [];

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

        foreach ($modules as $definition) {
            if (!$definition->isInstalled()) {
                //Non installed
                continue;
            }

            $installer = $definition->getInstaller();

            //We might need to user console logger and LoggerAwareInterface in future
            if ($this->isVerbose() && $installer instanceof LoggerAwareInterface) {
                $installer->setLogger($this->createLogger($definition));
            }

            $installer->update();
            $this->writeln(
                "Module '<comment>{$definition->getName()}</comment>' was successfully udpated."
            );
        }
    }
}