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
use Spiral\Helpers\StringHelper;

class ListCommand extends Command
{
    /**
     * Status texts.
     */
    const INTALLED      = '<info>installed</info>';
    const NOT_INSTALLED = '<fg=red>not installed</fg=red>';

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'modules:list';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Get list of all available and installed modules.';

    /**
     * Loaded composer.lock file.
     *
     * @var array
     */
    protected $composer = array();

    /**
     * Get list of all available modules.
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

        $table = $this->table(array(
            'Module:',
            'Version:',
            'Status:',
            'Size:',
            'Location:',
            'Description:'
        ));

        foreach ($modules as $module)
        {
            $table->addRow(array(
                $module->getName(),
                $this->getVersion($module->getName()),
                $module->isInstalled() ? self::INTALLED : self::NOT_INSTALLED,
                StringHelper::formatBytes($module->getSize()),
                $this->file->relativePath($module->getLocation()),
                wordwrap($module->getDescription())
            ));
        }

        $table->render();
    }

    /**
     * Get module version fetched from composer.lock file.
     *
     * @param string $module
     * @return string
     */
    protected function getVersion($module)
    {
        if (!$this->file->exists('composer.lock'))
        {
            //Usually spiral.cli called from project root level (not webroot).
            return 'undefined';
        }

        if (empty($this->composer))
        {
            $this->composer = json_decode($this->file->read('composer.lock'), true);
        }

        foreach ($this->composer['packages'] as $package)
        {
            if ($package['name'] == $module)
            {
                return $package['version'];
            }
        }

        return 'undefined';
    }
}