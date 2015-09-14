<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Modules;

use Spiral\Console\Command;
use Spiral\Support\StringHelper;
use Spiral\Tokenizer\TokenizerInterface;

/**
 * List every available module, it's version and installation status.
 */
class ModulesCommand extends Command
{
    /**
     * Status texts.
     */
    const INSTALLED     = '<info>installed</info>';
    const NOT_INSTALLED = '<fg=red>not installed</fg=red>';

    /**
     * {@inheritdoc}
     */
    protected $name = 'modules';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Get list of all available and installed modules.';

    /**
     * {@inheritdoc}
     */
    protected $composer = [];

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

        $table = $this->tableHelper([
            'Module:',
            'Version:',
            'Status:',
            'Size:',
            'Location:',
            'Description:'
        ]);

        foreach ($modules as $module) {
            $table->addRow([
                $module->getName(),
                $this->fetchVersion($module->getName()),
                $this->modules->hasModule($module->getName()) ? self::INSTALLED : self::NOT_INSTALLED,
                StringHelper::bytes($module->getSize()),
                $this->files->relativePath($module->getLocation(), directory('root')),
                wordwrap($module->getDescription())
            ]);
        }

        $table->render();
    }

    /**
     * Fetch module version fetched from composer.lock file.
     *
     * @param string $module
     * @return string
     */
    protected function fetchVersion($module)
    {
        if (!$this->files->exists('composer.lock')) {
            //Usually spiral.cli called from project root level (not webroot).
            return 'undefined';
        }

        if (empty($this->composer)) {
            $this->composer = json_decode($this->files->read('composer.lock'), true);
        }

        foreach ($this->composer['packages'] as $package) {
            if ($package['name'] == $module) {
                return $package['version'];
            }
        }

        return 'undefined';
    }
}