<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors\Templater;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\ViewException;
use Spiral\Components\View\ViewManager;

abstract class Import
{
    /**
     * Keyword to replace currently active namespace.
     */
    const SELF_NAMESPACE = 'self';

    /**
     * Import definition level, deeper in hierarchy import defined - higher level.
     *
     * @var int
     */
    protected $level = 0;

    /**
     * Every import can represent only one namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Copying import object to be used in another node, delta import used in cases if another node
     * is child one.
     *
     * @param int $deltaLevel How import level changed.
     * @return AliasImport
     */
    public function getCopy($deltaLevel = 0)
    {
        $import = clone $this;
        $import->level += $deltaLevel;

        return $import;
    }

    /**
     * Get import priority level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Will generate list of aliases associated with this import.
     *
     * @param ViewManager $manager
     * @param FileManager $file
     * @param string      $separator
     * @return array
     * @throws ViewException
     */
    abstract public function generateAliases(
        ViewManager $manager,
        FileManager $file,
        $separator = '.'
    );

    /**
     * Associated view namespace.
     *
     * @return array
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}