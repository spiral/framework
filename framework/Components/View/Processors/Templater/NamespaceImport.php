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

class NamespaceImport extends Import
{
    protected $directory = '';

    protected $outer = '';

    /**
     * Cached list of tag aliases.
     *
     * @var array
     */
    protected static $aliases = array();

    public function __construct($level, $namespace, $path, $name = '')
    {
        $this->level = $level;

        if (strpos($path, ':') !== false)
        {
            list($this->namespace, $this->directory) = explode(':', $path);
        }
        else
        {
            $this->namespace = $path;
        }

        $this->outer = $name;
        if (empty($this->outer))
        {
            $this->outer = $this->namespace;
        }

        if ($this->namespace == self::SELF_NAMESPACE)
        {
            //Let's use parent namespace
            $this->namespace = $namespace;
        }
    }

    /**
     * Unique import id, used to store cached view aliases separately from other imports.
     *
     * @return string
     */
    protected function aliasID()
    {
        return md5($this->namespace . '-' . $this->directory . '-' . $this->outer);
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
        if (isset(self::$aliases[$this->aliasID()]))
        {
            return self::$aliases[$this->aliasID()];
        }

        //Checking if namespace exists
        if (!isset($view->getNamespaces()[$this->namespace]))
        {
            throw new ViewException("Undefined view namespace '{$this->namespace}'.");
        }

        $directories = $view->getNamespaces()[$this->namespace];

        $aliases = array();
        foreach ($directories as $lookupDirectory)
        {
            $targetDirectory = $file->normalizePath($lookupDirectory . '/' . $this->directory);
            if ($file->exists($targetDirectory))
            {

                $viewFiles = $file->getFiles($targetDirectory, substr(ViewManager::EXTENSION, 1));

                //Getting views
                foreach ($viewFiles as $filename)
                {
                    //Filename in namespace
                    $name = $file->relativePath($filename, $targetDirectory);
                    $filename = $file->relativePath($filename, $lookupDirectory);

                    //Removing ./ and .php
                    $name = str_replace(array('/', '\\'), $separator, substr($name, 2, -4));
                    $filename = str_replace(array('/', '\\'), $separator, substr($filename, 2, -4));

                    //Registering alias
                    $aliases[$this->outer . ':' . $name] = $this->namespace . ':' . $filename;
                }
            }
        }

        if (!$aliases)
        {
            throw new ViewException(
                "No views were found under directory '{$this->directory}' in namespace '{$this->namespace}'."
            );
        }

        return self::$aliases[$this->aliasID()] = $aliases;
    }
}