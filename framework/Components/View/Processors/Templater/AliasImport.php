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
use Spiral\Components\View\ViewManager;
use Spiral\Components\View\ViewException;

class AliasImport extends Import
{
    protected $view = '';

    protected $alias = '';

    public function __construct($level, $namespace, $path, $alias = '')
    {
        $this->level = $level;

        //Parsing path
        if (strpos($path, ':') === false)
        {
            throw new ViewException(
                "Import path should always include namespace. Use 'self' to import from current namespace."
            );
        }

        list($this->namespace, $this->view) = explode(':', $path);
        if ($this->namespace == self::SELF_NAMESPACE)
        {
            //Let's use parent namespace
            $this->namespace = $namespace;
        }

        $this->alias = $alias;
        if (empty($this->alias))
        {
            $this->alias = $this->view;
        }
    }

    /**
     * Will generate list of aliases associated with this import.
     *
     * @param ViewManager $view
     * @param FileManager $file
     * @param string      $separator
     * @return array
     * @throws ViewException
     */
    public function generateAliases(ViewManager $view, FileManager $file, $separator = '.')
    {
        return array(
            $this->alias => $this->namespace . ':' . $this->view
        );
    }
}